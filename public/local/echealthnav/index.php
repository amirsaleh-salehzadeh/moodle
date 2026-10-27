<?php
// Consolidated, capability checked access to native Moodle course features.
require('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

$view = optional_param('view', 'courses', PARAM_ALPHANUMEXT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$focus = optional_param('focus', '', PARAM_ALPHANUMEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$pageparams = ['view' => $view];
if ($focus !== '') {
    $pageparams['focus'] = $focus;
}
$PAGE->set_url(new moodle_url('/local/echealthnav/index.php', $pageparams));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('standard');

require_login();
$titles = [
    'learners' => 'Learner Management', 'exams' => 'Exams & Assessments',
    'assignments' => 'Assignments', 'attendance' => 'Attendance', 'reports' => 'Reports & Analytics',
    'certificates' => 'Certificates', 'courses' => 'Courses & Subjects', 'createquiz' => 'Create Exam',
    'createassign' => 'Create Assignment', 'communication' => 'Communication', 'enrolments' => 'Enrolments',
];
if (!isset($titles[$view])) {
    throw new moodle_exception('invalidparameter');
}

$canadmin = has_capability('moodle/site:config', $systemcontext);
$courses = $canadmin ? get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.visible, c.category') : enrol_get_my_courses('id, fullname, visible, category', 'sortorder ASC');
$courses = array_filter($courses, static function($course) {
    return (int)$course->id !== SITEID;
});
$coursecaps = static function($course, $capabilities) use ($canadmin) {
    if ($canadmin) {
        return true;
    }
    $context = context_course::instance($course->id);
    foreach ($capabilities as $capability) {
        if (has_capability($capability, $context)) {
            return true;
        }
    }
    return false;
};
$courseurl = static function($course, $target) {
    return new moodle_url('/course/view.php', ['id' => $course->id, 'ecnav' => $target]);
};

$PAGE->set_title($titles[$view]);
$PAGE->set_heading($titles[$view]);
$PAGE->navbar->add($titles[$view]);
echo $OUTPUT->header();
echo $OUTPUT->heading($titles[$view]);

if ($view === 'createquiz' || $view === 'createassign') {
    $module = $view === 'createquiz' ? 'quiz' : 'assign';
    $capability = 'mod/' . $module . ':addinstance';
    if ($courseid) {
        $course = get_course($courseid);
        require_login($course);
        require_capability($capability, context_course::instance($courseid));
        redirect(new moodle_url('/course/modedit.php', ['add' => $module, 'course' => $courseid, 'section' => 0]));
    }
    $items = [];
    foreach ($courses as $course) {
        if ((int)$course->id === SITEID) {
            continue;
        }
        if (!$coursecaps($course, [$capability])) {
            continue;
        }
        $items[] = html_writer::tag('li', html_writer::link(
            new moodle_url('/local/echealthnav/index.php', ['view' => $view, 'courseid' => $course->id]),
            format_string($course->fullname)));
    }
    echo $items ? html_writer::tag('ul', implode('', $items)) : $OUTPUT->notification(get_string('nocourses', 'theme_echealth'), 'info');
} else if ($view === 'learners') {
    $seen = [];
    $rows = [];
    $studentroleids = array_fill_keys(array_keys(get_archetype_roles('student')), true);
    foreach ($courses as $course) {
        if ((int)$course->id === SITEID) {
            continue;
        }
        $context = context_course::instance($course->id);
        if (!has_capability('moodle/course:viewparticipants', $context)) {
            continue;
        }
        if (!$studentroleids) {
            continue;
        }
        [$enrolledsql, $enrolledparams] = get_enrolled_sql($context);
        [$rolesql, $roleparams] = $DB->get_in_or_equal(array_keys($studentroleids), SQL_PARAMS_NAMED, 'studentrole');
        $params = array_merge($enrolledparams, $roleparams, ['contextid' => $context->id]);
        $learners = $DB->get_records_sql("SELECT DISTINCT u.id, u.firstname, u.lastname
                FROM {user} u
                JOIN ($enrolledsql) enrolled ON enrolled.id = u.id
                JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = :contextid
               WHERE ra.roleid $rolesql AND u.deleted = 0
            ORDER BY u.lastname, u.firstname", $params);
        foreach ($learners as $learner) {
            if (isset($seen[$learner->id])) {
                continue;
            }
            $seen[$learner->id] = true;
            $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/user/view.php', ['id' => $learner->id, 'course' => $course->id]), fullname($learner))) .
                html_writer::tag('td', format_string($course->fullname)));
        }
    }
    if ($rows) {
        echo html_writer::tag('table', html_writer::tag('thead', html_writer::tag('tr', html_writer::tag('th', 'Learner') . html_writer::tag('th', 'Course'))) .
            html_writer::tag('tbody', implode('', $rows)), ['class' => 'generaltable']);
    } else {
        echo $OUTPUT->notification('No learners are available in courses you can manage.', 'info');
    }
} else if ($view === 'enrolments') {
    $items = [];
    foreach ($courses as $course) {
        if ((int)$course->id === SITEID) {
            continue;
        }
        if ($canadmin || has_capability('moodle/course:enrolreview', context_course::instance($course->id))) {
            $items[] = html_writer::tag('li', html_writer::link(new moodle_url('/enrol/instances.php', ['id' => $course->id]), format_string($course->fullname)));
        }
    }
    echo $items ? html_writer::tag('ul', implode('', $items)) : $OUTPUT->notification('No enrolment settings are available for your courses.', 'info');
} else {
    if ($view === 'certificates' && isset(core_component::get_plugin_list('mod')['customcert'])) {
        redirect(new moodle_url('/mod/customcert/my_certificates.php'));
    }
    $modnames = ['exams' => ['quiz'], 'assignments' => ['assign'], 'attendance' => ['attendance'], 'certificates' => ['customcert']];
    $capmap = ['quiz' => ['mod/quiz:attempt', 'mod/quiz:viewreports'], 'assign' => ['mod/assign:submit', 'mod/assign:grade'],
        'attendance' => ['mod/attendance:view'], 'customcert' => ['mod/customcert:view']];
    $rows = [];
    if (isset($modnames[$view])) {
        $installed = core_component::get_plugin_list('mod');
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $modinfo = get_fast_modinfo($course);
            foreach ($modnames[$view] as $modname) {
                if (!isset($installed[$modname]) || !$coursecaps($course, $capmap[$modname])) {
                    continue;
                }
                foreach ($modinfo->get_instances_of($modname) as $cm) {
                    if (!$cm->uservisible) {
                        continue;
                    }
                    $modulecontext = context_module::instance($cm->id);
                    if (!array_filter($capmap[$modname], static fn($cap) => has_capability($cap, $modulecontext))) {
                        continue;
                    }
                    $activitylink = html_writer::link($cm->url, format_string($cm->name));
                    $actions = [];
                    if ($modname === 'quiz') {
                        if (has_capability('mod/quiz:attempt', $modulecontext)) {
                            $actions[] = html_writer::link($cm->url, get_string('attempts', 'theme_echealth'));
                        }
                        if (has_capability('mod/quiz:viewreports', $modulecontext)) {
                            $actions[] = html_writer::link(new moodle_url('/mod/quiz/report.php', ['id' => $cm->id]), get_string('resultsgrades', 'theme_echealth'));
                        }
                    } else if ($modname === 'assign' && has_capability('mod/assign:grade', $modulecontext)) {
                        $actions[] = html_writer::link(new moodle_url('/mod/assign/view.php', ['id' => $cm->id, 'action' => 'grading']), get_string('submissions', 'theme_echealth'));
                    } else if ($modname === 'attendance') {
                        if (has_capability('mod/attendance:manageattendances', $modulecontext)) {
                            $actions[] = html_writer::link(new moodle_url('/mod/attendance/sessions.php', ['id' => $cm->id, 'action' => 1]), get_string('sessions', 'theme_echealth'));
                        }
                        if (has_capability('mod/attendance:viewreports', $modulecontext)) {
                            $actions[] = html_writer::link(new moodle_url('/mod/attendance/report.php', ['id' => $cm->id]), get_string('attendancereports', 'theme_echealth'));
                        }
                    }
                    $rows[] = html_writer::tag('tr', html_writer::tag('td', $activitylink) .
                        html_writer::tag('td', html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), format_string($course->fullname))) .
                        html_writer::tag('td', s(get_string('pluginname', 'mod_' . $modname))) .
                        html_writer::tag('td', implode(' · ', $actions)));
                }
            }
        }
    } else if ($view === 'reports') {
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            if (($focus === '' || $focus === 'learners') && has_capability('moodle/course:viewparticipants', $context)) {
                $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/user/index.php', ['id' => $course->id]), 'Learner list')) .
                    html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Learners') . html_writer::tag('td', ''));
            }
            if (($focus === '' || $focus === 'learners') && !empty($CFG->enablecompletion) && has_capability('report/progress:view', $context) && file_exists($CFG->dirroot . '/report/progress/index.php')) {
                $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/report/progress/index.php', ['course' => $course->id]), 'Learner progress')) .
                    html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Progress') . html_writer::tag('td', ''));
            }
            if (($focus === '' || $focus === 'exams') && has_capability('moodle/site:viewreports', $context)) {
                $modinfo = get_fast_modinfo($course);
                foreach ($modinfo->get_instances_of('quiz') as $cm) {
                    if ($cm->uservisible && has_capability('mod/quiz:viewreports', context_module::instance($cm->id))) {
                        $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/mod/quiz/report.php', ['id' => $cm->id]), format_string($cm->name))) .
                            html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Exam') . html_writer::tag('td', ''));
                    }
                }
            }
            if (($focus === '' || $focus === 'attendance') && isset(core_component::get_plugin_list('mod')['attendance'])) {
                $modinfo = get_fast_modinfo($course);
                foreach ($modinfo->get_instances_of('attendance') as $cm) {
                    if ($cm->uservisible && has_capability('mod/attendance:viewreports', context_module::instance($cm->id))) {
                        $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/mod/attendance/report.php', ['id' => $cm->id]), format_string($cm->name))) .
                            html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Attendance') . html_writer::tag('td', ''));
                    }
                }
            }
            if (($focus === '' || $focus === 'completion') && !empty($course->enablecompletion) && has_capability('report/completion:view', $context) && file_exists($CFG->dirroot . '/report/completion/index.php')) {
                $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/report/completion/index.php', ['course' => $course->id]), 'Completion report')) .
                    html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Completion') . html_writer::tag('td', ''));
            }
            if ($focus === '' && has_capability('moodle/site:viewreports', $context)) {
                $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/report/log/index.php', ['id' => $course->id]), 'Activity logs')) .
                    html_writer::tag('td', format_string($course->fullname)) . html_writer::tag('td', 'Reports') . html_writer::tag('td', ''));
            }
        }
    } else if ($view === 'courses') {
        foreach ($courses as $course) {
            if ((int)$course->id === SITEID) {
                continue;
            }
            $rows[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), format_string($course->fullname))) .
                html_writer::tag('td', $course->visible ? 'Visible' : 'Hidden') . html_writer::tag('td', 'Course'));
        }
    }
    if ($rows) {
        echo html_writer::tag('table', html_writer::tag('thead', html_writer::tag('tr', html_writer::tag('th', 'Activity') . html_writer::tag('th', 'Course') . html_writer::tag('th', 'Type') . html_writer::tag('th', 'Actions'))) .
            html_writer::tag('tbody', implode('', $rows)), ['class' => 'generaltable']);
    } else {
        echo $OUTPUT->notification('No matching items are available for your account.', 'info');
    }
}
echo $OUTPUT->footer();
