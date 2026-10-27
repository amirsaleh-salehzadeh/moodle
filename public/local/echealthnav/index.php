<?php
// Consolidated, capability checked access to native Moodle course features.
require('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

$view = optional_param('view', 'courses', PARAM_ALPHANUMEXT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$focus = optional_param('focus', '', PARAM_ALPHANUMEXT);
$selectedcourseid = optional_param('selectedcourse', 0, PARAM_INT);
$categoryid = optional_param('category', 0, PARAM_INT);
$query = optional_param('q', '', PARAM_TEXT);
$filtercourse = optional_param('filtercourse', 0, PARAM_INT);
$filterstatus = optional_param('status', '', PARAM_ALPHA);
$sort = optional_param('sort', 'title', PARAM_ALPHA);
$direction = optional_param('dir', 'asc', PARAM_ALPHA);
$perpage = optional_param('perpage', 25, PARAM_INT);
$pageindex = optional_param('p', 0, PARAM_INT);
$perpage = in_array($perpage, [10, 25, 50, 100], true) ? $perpage : 25;
$direction = $direction === 'desc' ? 'desc' : 'asc';
$systemcontext = context_system::instance();
$pageparams = ['view' => $view];
if ($focus !== '') {
    $pageparams['focus'] = $focus;
}
$PAGE->set_url(new moodle_url('/local/echealthnav/index.php', $pageparams));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('report');
$PAGE->add_body_class('ec-management-page');

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
$iscourseselector = in_array($view, ['createquiz', 'createassign'], true);
$courses = $iscourseselector ? [] : ($canadmin ? get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.visible, c.category') : enrol_get_my_courses('id, fullname, shortname, visible, category', 'sortorder ASC'));
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

// Continue from the course selection only after a CSRF check and a fresh capability check.
if (in_array($view, ['createquiz', 'createassign'], true) && $selectedcourseid && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_sesskey();
    $module = $view === 'createquiz' ? 'quiz' : 'assign';
    $selectedcourse = get_course($selectedcourseid);
    require_login($selectedcourse);
    require_capability('mod/' . $module . ':addinstance', context_course::instance($selectedcourseid));
    redirect(new moodle_url('/course/modedit.php', ['add' => $module, 'course' => $selectedcourseid, 'section' => 0]));
}

$PAGE->set_title($titles[$view]);
$PAGE->set_heading($titles[$view]);
$PAGE->navbar->add($titles[$view]);
echo $OUTPUT->header();

$descriptions = [
    'learners' => 'Find learners in courses you are authorised to manage.',
    'exams' => 'Review quizzes and assessments available in your courses.',
    'assignments' => 'Review assignment activities and open their native grading pages.',
    'attendance' => 'Open attendance activities and authorised reports.',
    'reports' => 'Find Moodle reports available for your courses and role.',
    'certificates' => 'Review certificate activities available to your account.',
    'courses' => 'Browse courses you can access and open their Moodle course pages.',
    'createquiz' => 'Choose a course where you have permission to create a Quiz. Moodle will open the native Quiz settings form next.',
    'createassign' => 'Choose a course where you have permission to create an Assignment.',
    'communication' => 'Open communication tools available to your account.',
    'enrolments' => 'Manage enrolment settings for courses where you have permission.',
];
$primaryaction = null;
if ($view === 'exams' || $view === 'assignments') {
    $module = $view === 'exams' ? 'quiz' : 'assign';
    foreach ($courses as $candidatecourse) {
        if ($coursecaps($candidatecourse, ['mod/' . $module . ':addinstance'])) {
            $primaryaction = [
                'url' => (new moodle_url('/local/echealthnav/index.php', ['view' => $view === 'exams' ? 'createquiz' : 'createassign']))->out(false),
                'label' => $view === 'exams' ? 'Create Exam' : 'Create Assignment',
            ];
            break;
        }
    }
}
echo $OUTPUT->render_from_template('local_echealthnav/page_intro', [
    'description' => $descriptions[$view],
    'primaryaction' => $primaryaction,
]);

// Shared server-side search, filters, sorting and pagination for portal listings.
$renderlisting = static function(array $htmlrows, array $courseoptions, string $title) use (
    $OUTPUT, $view, $focus, $query, $filtercourse, $filterstatus, $sort, $direction, $perpage, $pageindex
): void {
    $filtered = array_values(array_filter($htmlrows, static function($row) use ($query, $filtercourse, $filterstatus, $courseoptions) {
        $plain = html_entity_decode(strip_tags($row), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($query !== '' && stripos($plain, $query) === false) {
            return false;
        }
        $selectedcourse = isset($courseoptions[$filtercourse]) ? html_entity_decode(strip_tags($courseoptions[$filtercourse]), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
        if ($filtercourse && $selectedcourse !== '' && stripos($plain, $selectedcourse) === false) {
            return false;
        }
        if ($filterstatus !== '' && stripos($plain, $filterstatus) === false) {
            return false;
        }
        return true;
    }));
    usort($filtered, static function($a, $b) use ($direction, $sort) {
        preg_match_all('~<td\b[^>]*>(.*?)</td>~is', $a, $leftcells);
        preg_match_all('~<td\b[^>]*>(.*?)</td>~is', $b, $rightcells);
        $column = $sort === 'course' && count($leftcells[1]) > 1 ? 1 : 0;
        $left = html_entity_decode(strip_tags($leftcells[1][$column] ?? $a), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $right = html_entity_decode(strip_tags($rightcells[1][$column] ?? $b), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $result = strnatcasecmp($left, $right);
        return $direction === 'desc' ? -$result : $result;
    });
    $total = count($filtered);
    $lastpage = max(0, (int)ceil($total / $perpage) - 1);
    $currentpage = min(max(0, $pageindex), $lastpage);
    $params = ['view' => $view, 'q' => $query, 'filtercourse' => $filtercourse, 'status' => $filterstatus,
        'sort' => $sort, 'dir' => $direction, 'perpage' => $perpage];
    $url = new moodle_url('/local/echealthnav/index.php', $params);
    $formaction = new moodle_url('/local/echealthnav/index.php', ['view' => $view, 'focus' => $focus]);
    $form = html_writer::start_tag('form', ['method' => 'get', 'action' => $formaction->out(false), 'class' => 'ec-page-toolbar ec-listing-filters mb-3']);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'view', 'value' => $view]);
    if ($focus !== '') {
        $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'focus', 'value' => $focus]);
    }
    $form .= html_writer::label('Search', 'ec-search', false, ['class' => 'visually-hidden']);
    $form .= html_writer::empty_tag('input', ['id' => 'ec-search', 'type' => 'search', 'name' => 'q', 'value' => $query,
        'class' => 'form-control', 'placeholder' => 'Search ' . strtolower($title)]);
    if ($courseoptions) {
        $options = [0 => 'All courses'] + $courseoptions;
        $form .= html_writer::select($options, 'filtercourse', $filtercourse, false, ['class' => 'form-select', 'aria-label' => 'Filter by course']);
    }
    if (in_array($view, ['courses', 'exams', 'assignments', 'attendance'], true)) {
        $form .= html_writer::select(['' => 'All statuses', 'visible' => 'Visible', 'hidden' => 'Hidden'],
            'status', $filterstatus, false, ['class' => 'form-select', 'aria-label' => 'Filter by status']);
    }
    $form .= html_writer::select(['title' => 'Sort by title', 'course' => 'Sort by course'],
        'sort', $sort, false, ['class' => 'form-select', 'aria-label' => 'Sort results']);
    $form .= html_writer::select(['asc' => 'Ascending', 'desc' => 'Descending'],
        'dir', $direction, false, ['class' => 'form-select', 'aria-label' => 'Sort direction']);
    $form .= html_writer::select([10 => '10 per page', 25 => '25 per page', 50 => '50 per page', 100 => '100 per page'],
        'perpage', $perpage, false, ['class' => 'form-select', 'aria-label' => 'Results per page']);
    $form .= html_writer::tag('button', 'Apply', ['type' => 'submit', 'class' => 'btn btn-primary']);
    if ($query !== '' || $filtercourse || $filterstatus !== '') {
        $form .= html_writer::link(new moodle_url('/local/echealthnav/index.php', ['view' => $view, 'focus' => $focus]),
            'Clear filters', ['class' => 'btn btn-outline-secondary']);
    }
    $form .= html_writer::end_tag('form');
    echo $form;
    echo html_writer::div('Showing ' . ($total ? ($currentpage * $perpage + 1) . '–' . min($total, ($currentpage + 1) * $perpage) : '0') . ' of ' . $total . ' results', 'text-muted small mb-2');
    if ($total) {
        $headers = match ($view) {
            'learners' => ['Learner', 'Course'],
            'createquiz', 'createassign', 'enrolments' => ['Course'],
            'courses' => ['Course', 'Visibility', 'Type'],
            'reports' => ['Report', 'Course', 'Type', 'Actions'],
            'exams', 'assignments', 'attendance', 'certificates' => ['Activity', 'Course', 'Type', 'Visibility', 'Actions'],
            default => ['Activity', 'Course', 'Type', 'Actions'],
        };
        $head = html_writer::tag('thead', html_writer::tag('tr', implode('', array_map(static fn($label) => html_writer::tag('th', $label, ['scope' => 'col']), $headers))));
        $table = html_writer::tag('table', $head . html_writer::tag('tbody', implode('', array_slice($filtered, $currentpage * $perpage, $perpage))), ['class' => 'generaltable']);
        echo html_writer::div($table, 'table-responsive');
        echo $OUTPUT->paging_bar($total, $currentpage, $perpage, $url, 'p');
    } else {
        echo $OUTPUT->notification('No matching items are available. Try changing your search or filters.', 'info');
    }
};
$courseoptions = [];
foreach ($courses as $optioncourse) {
    if ((int)$optioncourse->id !== SITEID) {
        $courseoptions[$optioncourse->id] = format_string($optioncourse->fullname);
    }
}

if ($view === 'createquiz' || $view === 'createassign') {
    $module = $view === 'createquiz' ? 'quiz' : 'assign';
    $capability = 'mod/' . $module . ':addinstance';
    $categoryoptions = core_course_category::make_categories_list();
    $searchconditions = [];
    $searchparams = [];
    if ($categoryid && array_key_exists($categoryid, $categoryoptions)) {
        $searchconditions[] = 'c.category = :ec_category';
        $searchparams['ec_category'] = $categoryid;
    }
    $searchterms = $query === '' ? [] : preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);
    $totalcourses = 0;
    $currentcoursepage = max(0, $pageindex);
    $eligiblecourses = get_courses_search($searchterms, 'c.fullname ASC', $currentcoursepage, $perpage,
        $totalcourses, [$capability], $searchconditions, $searchparams);
    $selectorurl = new moodle_url('/local/echealthnav/index.php', ['view' => $view]);
    $filterform = html_writer::start_tag('form', ['method' => 'get', 'action' => $selectorurl->out(false), 'class' => 'ec-page-toolbar']);
    $filterform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'view', 'value' => $view]);
    $filterform .= html_writer::label('Search courses', 'ec-course-search', false, ['class' => 'visually-hidden']);
    $filterform .= html_writer::empty_tag('input', ['id' => 'ec-course-search', 'type' => 'search', 'name' => 'q', 'value' => $query,
        'class' => 'form-control ec-search-field', 'placeholder' => 'Search course name or code']);
    if ($categoryoptions) {
        $filterform .= html_writer::select([0 => 'All categories'] + $categoryoptions, 'category', $categoryid, false,
            ['class' => 'form-select', 'aria-label' => 'Filter by course category']);
    }
    $filterform .= html_writer::select([10 => '10 per page', 25 => '25 per page', 50 => '50 per page', 100 => '100 per page'], 'perpage', $perpage, false,
        ['class' => 'form-select', 'aria-label' => 'Courses per page']);
    $filterform .= html_writer::tag('button', 'Search', ['type' => 'submit', 'class' => 'btn btn-primary']);
    if ($query !== '' || $categoryid) {
        $filterform .= html_writer::link(new moodle_url('/local/echealthnav/index.php', ['view' => $view]), 'Clear filters', ['class' => 'btn btn-outline-secondary']);
    }
    $filterform .= html_writer::end_tag('form');
    echo $filterform;
    echo html_writer::div('Step 1 of 2 · Select a course', 'ec-workflow-step');
    echo html_writer::div('Showing ' . ($totalcourses ? ($currentcoursepage * $perpage + 1) . '–' . min($totalcourses, ($currentcoursepage + 1) * $perpage) : '0') . ' of ' . $totalcourses . ' courses', 'text-muted small mb-2');
    if ($totalcourses) {
        $cardform = html_writer::start_tag('form', ['method' => 'post', 'action' => $selectorurl->out(false)]);
        $cardform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'view', 'value' => $view]);
        $cardform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        $cardform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'q', 'value' => $query]);
        $cardform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'category', 'value' => $categoryid]);
        $cardform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'perpage', 'value' => $perpage]);
        $cardform .= html_writer::start_div('ec-course-selector-grid');
        foreach ($eligiblecourses as $course) {
            $category = !empty($course->category) ? core_course_category::get($course->category, IGNORE_MISSING) : null;
            $cardform .= $OUTPUT->render_from_template('local_echealthnav/course_selector_card', [
                'inputid' => 'course-' . $course->id,
                'courseid' => $course->id,
                'fullname' => format_string($course->fullname),
                'categoryname' => $category ? format_string($category->name) : 'Uncategorised',
                'shortname' => format_string($course->shortname ?? ''),
            ]);
        }
        $cardform .= html_writer::end_div();
        $cardform .= html_writer::tag('button', 'Continue', ['type' => 'submit', 'class' => 'btn btn-primary mt-3']);
        $cardform .= html_writer::end_tag('form');
        echo $cardform;
        echo $OUTPUT->paging_bar($totalcourses, $currentcoursepage, $perpage, new moodle_url('/local/echealthnav/index.php', [
            'view' => $view, 'q' => $query, 'category' => $categoryid, 'perpage' => $perpage,
        ]), 'p');
    } else {
        echo html_writer::div('No courses are available for this activity with your current permissions or filters.', 'ec-empty-state alert alert-info');
    }
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
    $renderlisting($rows, $courseoptions, 'learners');
} else if ($view === 'enrolments') {
    $items = [];
    foreach ($courses as $course) {
        if ((int)$course->id === SITEID) {
            continue;
        }
        if ($canadmin || has_capability('moodle/course:enrolreview', context_course::instance($course->id))) {
            $items[] = html_writer::tag('tr', html_writer::tag('td', html_writer::link(new moodle_url('/enrol/instances.php', ['id' => $course->id]), format_string($course->fullname))));
        }
    }
    $renderlisting($items, $courseoptions, 'enrolments');
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
                    $visibility = $cm->visible ? 'Visible' : 'Hidden';
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
                        html_writer::tag('td', $visibility) .
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
    $renderlisting($rows, $courseoptions, $titles[$view]);
}
echo $OUTPUT->footer();
