<?php
// Add a core Moodle learning page to each seeded nursing course.
define('CLI_SCRIPT', true);
set_time_limit(0);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/resourcelib.php');
global $DB;
$admin = $DB->get_record('user', ['username' => 'admin', 'deleted' => 0], '*', MUST_EXIST);
\core\session\manager::set_user($admin);
if ($CFG->dbname !== 'moodle_local_20261026' || parse_url($CFG->wwwroot, PHP_URL_HOST) !== 'localhost' ||
        strpos($CFG->dataroot, 'moodledata-moodle-checkout') === false) {
    cli_error('Refusing to modify a non-local Moodle site.');
}
$marker = $CFG->dataroot . DIRECTORY_SEPARATOR . 'echealth-demo-pages-created';
if (file_exists($marker)) {
    cli_error('Learning pages already created.');
}
$courses = $DB->get_records_select('course', "idnumber LIKE 'BUFFALO-CITY-%' OR idnumber LIKE 'NELSON-MANDELA-BAY-%' OR idnumber LIKE 'MTHATHA-%' OR idnumber LIKE 'KOMANI-%' OR idnumber LIKE 'MTHATHA-GATEWAY-%' OR idnumber LIKE 'PORT-ALFRED-%'");
if (count($courses) !== 36) {
    cli_error('Expected 36 demonstration courses before creating learning pages.');
}
foreach ($courses as $course) {
    [$module, $context, $cw, $cm, $data] = prepare_new_moduleinfo_data($course, 'page', 1);
    $data->name = 'Course learning guide';
    $data->introeditor = ['text' => '<p>Use this guide alongside the course sections and your educator’s direction.</p>', 'format' => FORMAT_HTML, 'itemid' => 0];
    $data->page = [
        'text' => '<h2>Learning guide</h2><p>' . s($course->fullname) . ' introduces foundational concepts for nursing education.</p>' .
            '<h3>How to use this course</h3><ol><li>Review each topic section and its learning outcomes.</li><li>Make notes on key concepts and questions to discuss with your educator.</li><li>Use only institution-approved resources when preparing for clinical practice.</li></ol>' .
            '<h3>Reflective prompt</h3><p>What information would you need to gather, and how would you communicate a concern respectfully and clearly?</p>' .
            '<p><strong>Demonstration notice:</strong> Fictional educational material for system familiarisation. It is not approved clinical guidance. Never enter identifying patient information into this demonstration site.</p>',
        'format' => FORMAT_HTML,
        'itemid' => 0,
    ];
    $data->content = $data->page['text'];
    $data->contentformat = FORMAT_HTML;
    $data->display = RESOURCELIB_DISPLAY_AUTO;
    $data->printintro = 0;
    $data->printlastmodified = 1;
    $data->revision = 1;
    add_moduleinfo($data, $course);
}
file_put_contents($marker, (string)time(), LOCK_EX);
echo 'Created core Moodle learning pages for ' . count($courses) . " courses.\n";
