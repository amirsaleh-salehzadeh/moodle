<?php
// Seed an isolated, fictional Eastern Cape Health Moodle demonstration.
// Run from the Moodle root with: php scripts/echealth_seed_demo.php
define('CLI_SCRIPT', true);
set_time_limit(0);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/enrol/manual/locallib.php');

global $DB;
$CFG->noemailever = true;

$adminuser = $DB->get_record('user', ['username' => 'admin', 'deleted' => 0], '*', MUST_EXIST);
\core\session\manager::set_user($adminuser);

if (parse_url($CFG->wwwroot, PHP_URL_HOST) !== 'localhost' || $CFG->dbname !== 'moodle_local_20261026' ||
        strpos($CFG->dataroot, 'moodledata-moodle-checkout') === false) {
    cli_error('Refusing to seed: this script is restricted to the isolated local Moodle checkout.');
}

$marker = $CFG->dataroot . DIRECTORY_SEPARATOR . 'echealth-demo-seeded.json';
if (file_exists($marker)) {
    cli_error('Demo seed already exists (marker found). No records were changed.');
}
if ($DB->count_records('user', ['deleted' => 0]) !== 2 || $DB->count_records('course') !== 1) {
    cli_error('Refusing seed because the initial user and course state has changed.');
}

$campuses = [
    ['buffalo-city', 'Buffalo City Nursing Campus'],
    ['nelson-mandela-bay', 'Nelson Mandela Bay Nursing Campus'],
    ['mthatha', 'Mthatha Nursing Campus'],
    ['komani', 'Komani Nursing Campus'],
    ['mthatha-gateway', 'Mthatha Gateway Nursing Campus'],
    ['port-alfred', 'Port Alfred Nursing Campus'],
];
$catalogue = [
    ['NUR101', 'Foundations of Nursing Practice', 'Core principles of person-centred nursing, professional conduct, communication and reflective practice.'],
    ['NUR112', 'Anatomy and Physiology for Nursing', 'An introduction to body systems, homeostasis and the language used to describe health and human biology.'],
    ['NUR124', 'Community and Primary Health Care', 'Health promotion, prevention, community assessment and the primary health care approach.'],
    ['NUR205', 'Maternal and Child Health', 'Learning resources on maternal, newborn and child health, with emphasis on respectful, evidence-informed care.'],
    ['NUR216', 'Medical-Surgical Nursing', 'Foundational learning on assessment, planning, safe practice and evaluation in adult nursing care.'],
    ['NUR230', 'Clinical Skills: Patient Observation', 'A fictional simulation course for recording observations, communicating concerns and receiving educator feedback.'],
];
$studentnames = [
    ['Amahle', 'Mbeki'], ['Lutho', 'Jali'], ['Sive', 'Mahlangu'], ['Ayanda', 'Tshabalala'],
    ['Zola', 'Maqoma'], ['Nolwazi', 'Kula'], ['Lindelwa', 'Mfenyana'], ['Anathi', 'Gaba'],
    ['Buhle', 'Koti'], ['Yanga', 'Poni'], ['Nandi', 'Vena'], ['Litha', 'Mkhize'],
];
$passwords = [];
$created = ['categories' => 0, 'courses' => 0, 'cohorts' => 0, 'users' => 0, 'enrolments' => 0];
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
$manual = enrol_get_plugin('manual');

foreach ($campuses as $campusindex => [$campusslug, $campusname]) {
    $categoryidnumber = 'ECHEALTH-' . strtoupper($campusslug);
    $categoryrecord = $DB->get_record('course_categories', ['idnumber' => $categoryidnumber]);
    if ($categoryrecord) {
        $category = core_course_category::get($categoryrecord->id);
    } else {
        $category = core_course_category::create((object)[
            'name' => $campusname,
            'idnumber' => $categoryidnumber,
            'description' => 'Fictional demonstration category for the Eastern Cape Health Nursing Learning Management System.',
            'descriptionformat' => FORMAT_HTML,
            'parent' => 0,
            'visible' => 1,
        ]);
        $created['categories']++;
    }
    $cohortidnumber = 'ECHEALTH-' . strtoupper($campusslug) . '-2026';
    $cohortrecord = $DB->get_record('cohort', ['idnumber' => $cohortidnumber, 'contextid' => context_system::instance()->id]);
    if ($cohortrecord) {
        $cohortid = $cohortrecord->id;
    } else {
        $cohort = new stdClass();
        $cohort->name = $campusname . ' Demonstration Cohort';
        $cohort->idnumber = $cohortidnumber;
        $cohort->contextid = context_system::instance()->id;
        $cohort->description = 'Synthetic learners for a local LMS demonstration. No real student or patient records.';
        $cohort->descriptionformat = FORMAT_HTML;
        $cohortid = cohort_add_cohort($cohort);
        $created['cohorts']++;
    }

    $teacher = (object)[
        'auth' => 'manual', 'confirmed' => 1, 'mnethostid' => $CFG->mnet_localhost_id,
        'username' => 'educator.' . $campusslug, 'password' => random_string(24) . 'A7#',
        'firstname' => 'Demo Educator', 'lastname' => $campusname,
        'email' => 'educator.' . $campusslug . '@example.test', 'country' => 'ZA', 'lang' => 'en',
    ];
    $teacherid = user_create_user($teacher);
    $passwords[$teacher->username] = $teacher->password;
    $created['users']++;
    for ($i = 0; $i < 4; $i++) {
        [$first, $last] = $studentnames[($campusindex * 2 + $i) % count($studentnames)];
        $username = sprintf('learner.%s.%02d', $campusslug, $i + 1);
        $user = (object)[
            'auth' => 'manual', 'confirmed' => 1, 'mnethostid' => $CFG->mnet_localhost_id,
            'username' => $username, 'password' => random_string(24) . 'A7#',
            'firstname' => $first, 'lastname' => $last . ' Demo',
            'email' => $username . '@example.test', 'country' => 'ZA', 'lang' => 'en',
        ];
        $userid = user_create_user($user);
        $passwords[$username] = $user->password;
        cohort_add_member($cohortid, $userid);
        $created['users']++;
    }

    foreach ($catalogue as [$code, $name, $description]) {
        $course = create_course((object)[
            'category' => $category->id,
            'fullname' => $name,
            'shortname' => $campusslug . '-' . strtolower($code),
            'idnumber' => strtoupper($campusslug) . '-' . $code,
            'summary' => '<p>' . s($description) . '</p><p><strong>Demonstration notice:</strong> Fictional learning content for system familiarisation. This is not approved clinical guidance and contains no patient records.</p>',
            'summaryformat' => FORMAT_HTML,
            'format' => 'topics',
            'numsections' => 4,
            'visible' => 1,
            'enablecompletion' => 1,
        ]);
        $created['courses']++;
        $sections = [
            'Welcome and learning outcomes' => 'Review the course orientation and learning outcomes. Activities in this course are illustrative and should be supplemented by approved institutional materials.',
            'Core concepts' => $description . ' Use your institution-approved learning resources to deepen this topic.',
            'Apply and reflect' => 'Use the fictional learning scenario to practise structured reasoning. Do not enter identifying information about any real person.',
            'Review and next steps' => 'Summarise what you learned, identify questions for your educator and consult current approved guidance before applying learning in practice.',
        ];
        $sectionnum = 1;
        foreach ($sections as $title => $content) {
            $sectionrecord = get_fast_modinfo($course)->get_section_info($sectionnum, MUST_EXIST);
            course_update_section($course, $sectionrecord, (object)[
                'name' => $title,
                'summary' => '<p>' . s($content) . '</p>',
                'summaryformat' => FORMAT_HTML,
            ]);
            $sectionnum++;
        }
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $manual->enrol_user($instance, $teacherid, $teacherrole->id);
        $created['enrolments']++;
        $learners = $DB->get_records_sql("SELECT u.id FROM {cohort_members} cm JOIN {user} u ON u.id=cm.userid WHERE cm.cohortid=?", [$cohortid]);
        foreach ($learners as $learner) {
            $manual->enrol_user($instance, $learner->id, $studentrole->id);
            $created['enrolments']++;
        }
    }
}

$output = json_encode(['created' => $created, 'accounts' => $passwords], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($marker, $output, LOCK_EX) === false) {
    cli_error('Seeded records, but could not securely write the demo credential file under Moodle data.');
}
@chmod($marker, 0600);
echo "Demo catalogue created. Synthetic demo credentials saved outside the web root in Moodle data.\n";
echo json_encode($created, JSON_PRETTY_PRINT) . "\n";
