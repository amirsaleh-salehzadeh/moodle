<?php
// Add Moodle-native assessment activities to the fictional nursing courses.
define('CLI_SCRIPT', true); set_time_limit(0);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/resourcelib.php');
global $DB;
$admin = $DB->get_record('user', ['username' => 'admin', 'deleted' => 0], '*', MUST_EXIST);
\core\session\manager::set_user($admin);
if ($CFG->dbname !== 'moodle_local_20261026' || parse_url($CFG->wwwroot, PHP_URL_HOST) !== 'localhost' ||
        strpos($CFG->dataroot, 'moodledata-moodle-checkout') === false) cli_error('Refusing non-local site.');
$marker = $CFG->dataroot . DIRECTORY_SEPARATOR . 'echealth-demo-assessments-created';
if (file_exists($marker)) cli_error('Assessment activities already created.');
$courses = $DB->get_records_select('course', "idnumber LIKE 'BUFFALO-CITY-%' OR idnumber LIKE 'NELSON-MANDELA-BAY-%' OR idnumber LIKE 'MTHATHA-%' OR idnumber LIKE 'KOMANI-%' OR idnumber LIKE 'MTHATHA-GATEWAY-%' OR idnumber LIKE 'PORT-ALFRED-%'");
if (count($courses) !== 36) cli_error('Expected 36 demo courses.');
foreach ($courses as $course) {
    [$module, $context, $cw, $cm, $data] = prepare_new_moduleinfo_data($course, 'assign', 3);
    $data->name = 'Reflective learning submission';
    $data->intro = '<p>Submit a short reflection on how clear communication, respectful practice and educator guidance support safe learning. Use a fictional scenario only.</p><p><strong>Do not include any patient-identifying information. This activity is illustrative and is not clinical guidance.</strong></p>';
    $data->introformat = FORMAT_HTML;
    $data->introeditor = ['text' => $data->intro, 'format' => FORMAT_HTML, 'itemid' => 0];
    $data->activityeditor = ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0];
    $data->submissiondrafts = 0;
    $data->requiresubmissionstatement = 0;
    $data->sendnotifications = 0;
    $data->sendlatenotifications = 0;
    $data->sendstudentnotifications = 0;
    $data->duedate = 0;
    $data->cutoffdate = 0;
    $data->gradingduedate = 0;
    $data->allowsubmissionsfromdate = 0;
    $data->grade = 100;
    $data->teamsubmission = 0;
    $data->requireallteammemberssubmit = 0;
    $data->blindmarking = 0;
    $data->markingworkflow = 0;
    $data->markingallocation = 0;
    $data->markinganonymous = 0;
    $data->maxattempts = 1;
    $data->assignsubmission_onlinetext_enabled = 1;
    $data->assignsubmission_file_enabled = 1;
    $data->assignsubmission_file_maxfiles = 2;
    $data->assignsubmission_file_maxsizebytes = 10485760;
    $data->assignsubmission_onlinetext_wordlimit_enabled = 0;
    $data->assignsubmission_onlinetext_wordlimit = 0;
    $data->assignfeedback_comments_enabled = 1;
    $data->completion = COMPLETION_TRACKING_AUTOMATIC;
    $data->completionsubmit = 1;
    add_moduleinfo($data, $course);
}
file_put_contents($marker, (string)time(), LOCK_EX);
echo 'Created Moodle assignment activities for ' . count($courses) . " courses.\n";
