<?php
// Complete the fictional local proof-of-concept accounts and front page.
define('CLI_SCRIPT', true);
set_time_limit(0);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/enrol/manual/locallib.php');
global $DB;
$CFG->noemailever = true;
$admin = $DB->get_record('user', ['username' => 'admin', 'deleted' => 0], '*', MUST_EXIST);
\core\session\manager::set_user($admin);
if ($CFG->dbname !== 'moodle_local_20261026' || parse_url($CFG->wwwroot, PHP_URL_HOST) !== 'localhost' ||
        strpos($CFG->dataroot, 'moodledata-moodle-checkout') === false) {
    cli_error('Refusing to modify a non-local Moodle site.');
}
$seedfile = $CFG->dataroot . DIRECTORY_SEPARATOR . 'echealth-demo-seeded.json';
$donefile = $CFG->dataroot . DIRECTORY_SEPARATOR . 'echealth-demo-completed.json';
if (!file_exists($seedfile) || file_exists($donefile)) {
    cli_error('Seed data is missing or this completion step already ran.');
}
$data = json_decode(file_get_contents($seedfile), true, 512, JSON_THROW_ON_ERROR);
if ($DB->count_records('course', ['idnumber' => 'BUFFALO-CITY-NUR101']) !== 1 ||
        $DB->count_records('cohort') < 6 || $DB->count_records('course') < 37) {
    cli_error('Expected seeded demo courses and cohorts were not found.');
}
$created = ['students' => 0, 'management_accounts' => 0, 'enrolments' => 0];
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$managerrole = $DB->get_record('role', ['shortname' => 'manager'], '*', MUST_EXIST);
$manual = enrol_get_plugin('manual');
$roster = [
    'buffalo-city' => [['Lerato', 'Demo']],
    'nelson-mandela-bay' => [['Siyamthanda', 'Demo']],
    'mthatha' => [['Zimkhitha', 'Demo']],
    'komani' => [['Onke', 'Demo']],
    'mthatha-gateway' => [['Lukhanyo', 'Demo']],
    'port-alfred' => [['Noluthando', 'Demo']],
];
foreach ($roster as $slug => $names) {
    $cohort = $DB->get_record('cohort', ['idnumber' => 'ECHEALTH-' . strtoupper($slug) . '-2026'], '*', MUST_EXIST);
    $campuscourses = $DB->get_records_select('course', 'idnumber LIKE ?', [strtoupper($slug) . '-%']);
    foreach ($names as $index => [$firstname, $lastname]) {
        $username = sprintf('learner.%s.%02d', $slug, 5 + $index);
        if ($DB->record_exists('user', ['username' => $username, 'deleted' => 0])) {
            continue;
        }
        $user = (object)[
            'auth' => 'manual', 'confirmed' => 1, 'mnethostid' => $CFG->mnet_localhost_id,
            'username' => $username, 'password' => random_string(24) . 'A7#',
            'firstname' => $firstname, 'lastname' => $lastname,
            'email' => $username . '@example.test', 'country' => 'ZA', 'lang' => 'en',
        ];
        $userid = user_create_user($user);
        $data['accounts'][$username] = $user->password;
        cohort_add_member($cohort->id, $userid);
        $created['students']++;
        foreach ($campuscourses as $course) {
            $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', MUST_EXIST);
            $manual->enrol_user($instance, $userid, $studentrole->id);
            $created['enrolments']++;
        }
    }
}
$management = [
    ['training.admin.1', 'Training Admin', 'One'],
    ['training.admin.2', 'Training Admin', 'Two'],
    ['training.manager', 'Training Manager', 'Demo'],
];
foreach ($management as [$username, $firstname, $lastname]) {
    if ($DB->record_exists('user', ['username' => $username, 'deleted' => 0])) {
        continue;
    }
    $user = (object)[
        'auth' => 'manual', 'confirmed' => 1, 'mnethostid' => $CFG->mnet_localhost_id,
        'username' => $username, 'password' => random_string(24) . 'A7#',
        'firstname' => $firstname, 'lastname' => $lastname,
        'email' => $username . '@example.test', 'country' => 'ZA', 'lang' => 'en',
    ];
    $userid = user_create_user($user);
    role_assign($managerrole->id, $userid, context_system::instance());
    $data['accounts'][$username] = $user->password;
    $created['management_accounts']++;
}

$DB->set_field('course', 'fullname', 'Eastern Cape Department of Health', ['id' => SITEID]);
$DB->set_field('course', 'shortname', 'Eastern Cape Health Learning Portal', ['id' => SITEID]);
$DB->set_field('course', 'summary', '<h2>Nursing Education &amp; Learning Management Portal</h2><p>Empowering healthcare professionals through accessible, high-quality education, practical skills development and continuous professional learning.</p><p><strong>Demonstration portal:</strong> Courses, accounts and learning content are illustrative. They are not official Department publications or approved clinical guidance.</p>', ['id' => SITEID]);
$DB->set_field('course', 'summaryformat', FORMAT_HTML, ['id' => SITEID]);
set_config('frontpage', '4,7'); // Course category catalogue and search.
set_config('frontpageloggedin', '4,5'); // Categories and each learner's enrolled courses.
set_config('frontpagecourselimit', 60);
$data['created']['students'] = 30;
$data['created']['management_accounts'] = 3;
file_put_contents($seedfile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
@chmod($seedfile, 0600);
file_put_contents($donefile, json_encode(['completed' => $created, 'time' => time()], JSON_PRETTY_PRINT), LOCK_EX);
@chmod($donefile, 0600);
echo "Demo roster and homepage configured. Credentials updated outside the web root.\n";
echo json_encode($created, JSON_PRETTY_PRINT) . "\n";
