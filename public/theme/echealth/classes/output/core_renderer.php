<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace theme_echealth\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Eastern Cape Health output additions built on Boost's accessible renderer.
 *
 * @package theme_echealth
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /** @var string|null Cached sidebar markup for this request. */
    private ?string $echealthnavigation = null;
    /** @var bool|null Cached category-scoped course management capability. */
    private ?bool $echealthcanmanagecourses = null;

    /**
     * Return the supplied logo, allowing a site administrator to replace it from theme settings.
     *
     * @return string
     */
    public function echealth_brand_logo(): string {
        $url = $this->page->theme->setting_file_url('logo', 'logo');
        if (empty($url)) {
            $url = $this->image_url('ec-health-logo', 'theme_echealth');
        }
        return \html_writer::empty_tag('img', [
            'src' => $url,
            'alt' => $this->echealth_department_name(),
            'class' => 'echealth-logo',
        ]);
    }

    /**
     * Return the configurable department name.
     *
     * @return string
     */
    public function echealth_department_name(): string {
        return format_string(get_config('theme_echealth', 'departmentname') ?: get_string('departmentname', 'theme_echealth'),
            true, ['context' => \context_system::instance(), 'escape' => false]);
    }

    /**
     * Return the configurable portal name.
     *
     * @return string
     */
    public function echealth_portal_name(): string {
        $name = get_config('theme_echealth', 'portalname');
        // Replace the previous theme default while keeping any site-specific label intact.
        if (empty($name) || $name === 'Department of Health LMS Portal') {
            $name = get_string('portalname', 'theme_echealth');
        }
        return format_string($name, true, ['context' => \context_system::instance(), 'escape' => false]);
    }

    /**
     * Build a capability-aware navigation from real Moodle destinations.
     *
     * The same markup is used in the desktop rail and Boost's focus-managed mobile drawer.
     *
     * @return string
     */
    public function echealth_navigation(): string {
        global $CFG, $DB, $USER;

        if ($this->echealthnavigation !== null) {
            return $this->echealthnavigation;
        }
        $systemcontext = \context_system::instance();
        $path = $this->page->url->get_path();
        $primary = [];
        $teaching = [];
        $learners = [];
        $management = [];
        $system = [];
        $support = [];
        $enrolledcourses = [];
        $coursemodules = [];
        $certificatecm = null;
        $optionalmodules = \core_component::get_plugin_list('mod');

        if (!isloggedin() || isguestuser()) {
            $this->add_echealth_nav_item($primary, 'sitehome', new \moodle_url('/'), 'home', $path === '/');
            $this->add_echealth_nav_item($primary, 'catalogue', new \moodle_url('/course/index.php'), 'catalogue', $path === '/course/index.php');
        } else {
            $issiteadmin = has_capability('moodle/site:config', $systemcontext);
            $enrolledcourses = enrol_get_my_courses('id, fullname, shortname', 'sortorder ASC', 30);
            // Only inspect course modules when optional navigation destinations are installed.
            if (isset($optionalmodules['customcert']) || isset($optionalmodules['attendance']) || isset($optionalmodules['forum'])) {
                foreach ($enrolledcourses as $course) {
                    $modinfo = get_fast_modinfo($course);
                    foreach ($modinfo->get_cms() as $cm) {
                        if (!$cm->uservisible || !isset($optionalmodules[$cm->modname])) {
                            continue;
                        }
                        $coursemodules[$course->id][] = $cm;
                        if ($cm->modname === 'customcert' && has_capability('mod/customcert:view', \context_module::instance($cm->id))) {
                            $iscurrentcertificate = $path === '/mod/customcert/view.php' &&
                                (int)$this->page->url->get_param('id') === (int)$cm->id;
                            if ($certificatecm === null || $iscurrentcertificate) {
                                $certificatecm = $cm;
                            }
                        }
                    }
                }
            }
            if ($certificatecm) {
                $certificateurl = new \moodle_url('/mod/customcert/view.php', ['id' => $certificatecm->id]);
                $this->add_echealth_nav_item($primary, 'certificates', $certificateurl, 'certificate',
                    $path === '/mod/customcert/view.php' && (int)$this->page->url->get_param('id') === (int)$certificatecm->id);
            }
            if (!isset($CFG->enabledashboard) || $CFG->enabledashboard) {
                $this->add_echealth_nav_item($primary, 'dashboard', new \moodle_url('/my/'), 'dashboard', in_array($path, ['/my/', '/my/index.php'], true));
            }
            $this->add_echealth_nav_item($primary, 'sitehome', new \moodle_url('/'), 'home', $path === '/');
            if (file_exists($CFG->dirroot . '/my/courses.php')) {
                $this->add_echealth_nav_item($primary, 'mycourses', new \moodle_url('/my/courses.php'), 'courses', $path === '/my/courses.php');
            }
            if (!empty($CFG->enableglobalsearch) && file_exists($CFG->dirroot . '/search/index.php')) {
                $this->add_echealth_nav_item($primary, 'search', new \moodle_url('/search/index.php'), 'search', str_starts_with($path, '/search/'));
            }
            $this->add_echealth_nav_item($primary, 'catalogue', new \moodle_url('/course/index.php'), 'catalogue', $path === '/course/index.php');
            $this->add_echealth_nav_item($primary, 'calendar', new \moodle_url('/calendar/view.php'), 'calendar', str_starts_with($path, '/calendar/'));
            if (file_exists($CFG->dirroot . '/message/output/popup/notifications.php')) {
                $this->add_echealth_nav_item($primary, 'viewnotifications',
                    new \moodle_url('/message/output/popup/notifications.php'), 'bell',
                    str_starts_with($path, '/message/output/popup/'));
            }
            if (!empty($CFG->enablebadges) && file_exists($CFG->dirroot . '/badges/mybadges.php') &&
                    has_capability('moodle/badges:manageownbadges', \context_user::instance($USER->id))) {
                $this->add_echealth_nav_item($primary, 'mybadges', new \moodle_url('/badges/mybadges.php'), 'award',
                    $path === '/badges/mybadges.php');
            }

            // Put the established Moodle destinations into the permanent portal rail.
            // Course activities are still resolved inside each course and filtered by
            // the same capabilities Moodle uses to protect those native pages.
            $admin = has_capability('moodle/site:config', $systemcontext);
            $portalurl = static function(string $view): \moodle_url {
                return new \moodle_url('/local/echealthnav/index.php', ['view' => $view]);
            };
            $learneritems = [];
            if ($admin || has_capability('moodle/user:create', $systemcontext) || has_capability('moodle/user:update', $systemcontext)) {
                $this->add_echealth_nav_item($learneritems, 'alllearners', $portalurl('learners'), 'users', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'learners');
            }
            $participantcourses = array_values(array_filter($enrolledcourses, static function($course) {
                return (int)$course->id !== SITEID && has_capability('moodle/course:viewparticipants', \context_course::instance($course->id));
            }));
            if ($participantcourses && !$learneritems) {
                $this->add_echealth_nav_item($learneritems, 'alllearners', $portalurl('learners'), 'users', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'learners');
            }
            if (file_exists($CFG->dirroot . '/user/editadvanced.php') && has_capability('moodle/user:create', $systemcontext)) {
                $this->add_echealth_nav_item($learneritems, 'registerlearner', new \moodle_url('/user/editadvanced.php', ['id' => -1]), 'plus', $path === '/user/editadvanced.php');
            }
            foreach ($enrolledcourses as $course) {
                if ((int)$course->id !== SITEID && has_capability('moodle/course:enrolreview', \context_course::instance($course->id))) {
                    $this->add_echealth_nav_item($learneritems, 'enrolments', new \moodle_url('/enrol/instances.php', ['id' => $course->id]), 'enrolment',
                        $path === '/enrol/instances.php' && (int)$this->page->url->get_param('id') === (int)$course->id);
                    break;
                }
            }
            if ($issiteadmin && file_exists($CFG->dirroot . '/enrol/instances.php') &&
                    !array_filter($learneritems, static fn($item) => $item['label'] === get_string('enrolments', 'theme_echealth'))) {
                $this->add_echealth_nav_item($learneritems, 'enrolments', $portalurl('enrolments'), 'enrolment',
                    $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'enrolments');
            }
            $hasgroupsdestination = false;
            if (has_capability('moodle/cohort:manage', $systemcontext)) {
                $this->add_echealth_nav_item($learneritems, 'groupscohorts', new \moodle_url('/cohort/index.php'), 'campus', $path === '/cohort/index.php');
                $hasgroupsdestination = true;
            }
            if (!$hasgroupsdestination) {
                foreach ($enrolledcourses as $course) {
                    if ((int)$course->id !== SITEID && has_capability('moodle/course:managegroups', \context_course::instance($course->id))) {
                        $this->add_echealth_nav_item($learneritems, 'groupscohorts', new \moodle_url('/group/index.php', ['id' => $course->id]), 'campus',
                            $path === '/group/index.php' && (int)$this->page->url->get_param('id') === (int)$course->id);
                        break;
                    }
                }
            }
            if (file_exists($CFG->dirroot . '/admin/tool/lp/competencies.php') && has_capability('tool/lp:competencymanage', $systemcontext)) {
                $this->add_echealth_nav_item($learneritems, 'competencies', new \moodle_url('/admin/tool/lp/competencies.php'), 'competency', str_starts_with($path, '/admin/tool/lp/'));
            }
            $canviewlearnerprogress = false;
            foreach ($enrolledcourses as $course) {
                if ((int)$course->id !== SITEID && has_capability('report/progress:view', \context_course::instance($course->id))) {
                    $canviewlearnerprogress = true;
                    break;
                }
            }
            if ($admin || $canviewlearnerprogress) {
                $this->add_echealth_nav_item($learneritems, 'learnerprogress', $portalurl('reports'), 'grades', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'reports');
            }
            if ($learneritems) {
                $learners = $learneritems;
            }

            $managedcourses = [];
            foreach ($enrolledcourses as $course) {
                if ((int)$course->id === SITEID) {
                    continue;
                }
                $context = \context_course::instance($course->id);
                $cancreatequiz = has_capability('mod/quiz:addinstance', $context);
                $cancreateassign = has_capability('mod/assign:addinstance', $context);
                if ($admin || $cancreatequiz || $cancreateassign || has_capability('moodle/course:update', $context)) {
                    $managedcourses[] = $course;
                }
            }

            $coursechildren = [];
            $this->add_echealth_nav_item($coursechildren, 'allcourses', $portalurl('courses'), 'book-open', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'courses');
            if ($admin || has_capability('moodle/course:create', $systemcontext)) {
                $this->add_echealth_nav_item($coursechildren, 'createcourse', new \moodle_url('/course/edit.php'), 'plus', $path === '/course/edit.php');
            }
            $this->add_echealth_nav_item($coursechildren, 'modulescontent', new \moodle_url('/my/courses.php'), 'book', $path === '/my/courses.php');
            $this->add_echealth_nav_item($coursechildren, 'categories', new \moodle_url('/course/index.php'), 'catalogue', $path === '/course/index.php');
            if ($coursechildren) {
                $teaching[] = ['label' => get_string('coursesubjects', 'theme_echealth'), 'url' => $portalurl('courses')->out(false),
                    'icon' => $this->image_url('icons/courses', 'theme_echealth')->out(false), 'active' => false, 'children' => $coursechildren];
            }

            $quizcourses = [];
            $quizreportcourses = [];
            $quizcreatecourses = [];
            $assigncourses = [];
            $gradecourses = [];
            $assigncreatecourses = [];
            $attcourses = [];
            $forcourses = [];
            foreach ($enrolledcourses as $course) {
                if ((int)$course->id === SITEID) {
                    continue;
                }
                $context = \context_course::instance($course->id);
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                if ($issiteadmin || has_capability('mod/quiz:attempt', $context) || has_capability('mod/quiz:viewreports', $context) || has_capability('mod/quiz:addinstance', $context)) {
                    $quizcourses[] = ['label' => format_string($course->fullname), 'url' => (new \moodle_url('/mod/quiz/index.php', ['id' => $course->id]))->out(false),
                        'icon' => $this->image_url('icons/quiz', 'theme_echealth')->out(false), 'active' => false];
                }
                if ($issiteadmin || has_capability('mod/quiz:viewreports', $context)) {
                    $quizreportcourses[] = $course;
                }
                if ($issiteadmin || has_capability('mod/quiz:addinstance', $context)) {
                    $quizcreatecourses[] = $course;
                }
                if ($issiteadmin || has_capability('mod/assign:submit', $context) || has_capability('mod/assign:grade', $context) || has_capability('mod/assign:addinstance', $context)) {
                    $assigncourses[] = ['label' => format_string($course->fullname), 'url' => (new \moodle_url('/mod/assign/index.php', ['id' => $course->id]))->out(false),
                        'icon' => $this->image_url('icons/clipboard', 'theme_echealth')->out(false), 'active' => false];
                }
                if ($issiteadmin || has_capability('mod/assign:grade', $context)) {
                    $gradecourses[] = $course;
                }
                if ($issiteadmin || has_capability('mod/assign:addinstance', $context)) {
                    $assigncreatecourses[] = $course;
                }
                if (isset($optionalmodules['attendance']) && ($issiteadmin || has_capability('mod/attendance:view', $context))) {
                    $attcourses[] = ['label' => format_string($course->fullname), 'url' => $courseurl->out(false),
                        'icon' => $this->image_url('icons/attendance', 'theme_echealth')->out(false), 'active' => false];
                }
            }
            $addsection = function(array &$items, string $label, string $view, string $icon, array $children = []) use ($portalurl, $path) {
                $active = $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === $view;
                $item = ['label' => $label, 'url' => $portalurl($view)->out(false), 'icon' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false), 'active' => $active];
                if ($children) {
                    $item['children'] = $children;
                }
                $items[] = $item;
            };
            if ($quizcourses || $managedcourses || $issiteadmin) {
                $children = [];
                $this->add_echealth_nav_item($children, 'allexams', $portalurl('exams'), 'quiz', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'exams');
                if ($quizcreatecourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'createexam', $portalurl('createquiz'), 'plus', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'createquiz');
                }
                $questioncourse = $issiteadmin ? get_site() : null;
                foreach ($enrolledcourses as $course) {
                    if ((int)$course->id !== SITEID && ($issiteadmin || has_capability('moodle/question:managecategory', \context_course::instance($course->id)))) {
                        $questioncourse = $course;
                        break;
                    }
                }
                if ($questioncourse) {
                    $this->add_echealth_nav_item($children, 'questionbank', new \moodle_url('/question/banks.php', ['courseid' => $questioncourse->id]), 'file-text', str_starts_with($path, '/question/'));
                }
                if ($quizcourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'attempts', new \moodle_url('/local/echealthnav/index.php', ['view' => 'exams', 'focus' => 'attempts']), 'activity', false);
                }
                if ($quizreportcourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'resultsgrades', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'exams']), 'grades', false);
                }
                $addsection($teaching, get_string('examsassessments', 'theme_echealth'), 'exams', 'quiz', $children);
            }
            if ($assigncourses || $managedcourses || $issiteadmin) {
                $children = [];
                $this->add_echealth_nav_item($children, 'allassignments', $portalurl('assignments'), 'clipboard', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'assignments');
                if ($assigncreatecourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'createassignment', $portalurl('createassign'), 'plus', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'createassign');
                }
                if ($gradecourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'submissions', $portalurl('assignments'), 'upload', false);
                    $this->add_echealth_nav_item($children, 'markingfeedback', $portalurl('assignments'), 'check-circle', false);
                }
                $addsection($teaching, get_string('assignmentssection', 'theme_echealth'), 'assignments', 'clipboard', $children);
            }
            if ($attcourses || ($issiteadmin && isset($optionalmodules['attendance']))) {
                $children = [];
                $this->add_echealth_nav_item($children, 'attendanceregister', $portalurl('attendance'), 'attendance', $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'attendance');
                if ($issiteadmin) {
                    $this->add_echealth_nav_item($children, 'sessions', new \moodle_url('/local/echealthnav/index.php', ['view' => 'attendance', 'focus' => 'sessions']), 'calendar',
                        $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'attendance' && $this->page->url->get_param('focus') === 'sessions');
                }
                foreach ($coursemodules as $modules) {
                    foreach ($modules as $cm) {
                        if ($cm->modname !== 'attendance' || !has_capability('mod/attendance:manageattendances', \context_module::instance($cm->id))) {
                            continue;
                        }
                        $this->add_echealth_nav_item($children, 'sessions', new \moodle_url('/mod/attendance/sessions.php', ['id' => $cm->id, 'action' => 1]), 'calendar', $path === '/mod/attendance/sessions.php' && (int)$this->page->url->get_param('id') === (int)$cm->id);
                    }
                }
                if ($attcourses || $issiteadmin) {
                    $this->add_echealth_nav_item($children, 'attendancereports', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'attendance']), 'reports', false);
                }
                $addsection($teaching, get_string('attendance', 'theme_echealth'), 'attendance', 'attendance', $children);
            }

            $hasreports = $admin;
            foreach ($enrolledcourses as $course) {
                if ((int)$course->id === SITEID) {
                    continue;
                }
                $context = \context_course::instance($course->id);
                if (has_capability('moodle/site:viewreports', $context) || has_capability('report/progress:view', $context) ||
                        has_capability('report/completion:view', $context)) {
                    $hasreports = true;
                    break;
                }
            }
            if ($hasreports) {
                $children = [];
                $isreportpage = $path === '/local/echealthnav/index.php' && $this->page->url->get_param('view') === 'reports';
                $reportfocus = (string)$this->page->url->get_param('focus');
                $this->add_echealth_nav_item($children, 'learnerreports', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'learners']), 'users', $isreportpage && in_array($reportfocus, ['', 'learners'], true));
                $this->add_echealth_nav_item($children, 'examreports', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'exams']), 'quiz', $isreportpage && $reportfocus === 'exams');
                $this->add_echealth_nav_item($children, 'attendanceanalyticsreports', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'attendance']), 'attendance', $isreportpage && $reportfocus === 'attendance');
                $this->add_echealth_nav_item($children, 'completionreports', new \moodle_url('/local/echealthnav/index.php', ['view' => 'reports', 'focus' => 'completion']), 'check-circle', $isreportpage && $reportfocus === 'completion');
                if ($issiteadmin && file_exists($CFG->dirroot . '/reportbuilder/index.php')) {
                    $this->add_echealth_nav_item($children, 'sitereports', new \moodle_url('/reportbuilder/index.php'), 'file-text', str_starts_with($path, '/reportbuilder/'));
                }
                $addsection($management, get_string('reportsanalytics', 'theme_echealth'), 'reports', 'reports', $children);
            }
            if (isset($optionalmodules['customcert'])) {
                $children = [];
                $this->add_echealth_nav_item($children, 'issuedcertificates', new \moodle_url('/mod/customcert/my_certificates.php'), 'certificate', str_starts_with($path, '/mod/customcert/my_certificates.php'));
                if (has_capability('mod/customcert:manage', $systemcontext)) {
                    $this->add_echealth_nav_item($children, 'certificatetemplates', new \moodle_url('/mod/customcert/manage_templates.php', ['contextid' => $systemcontext->id]), 'settings', $path === '/mod/customcert/manage_templates.php');
                }
                $addsection($management, get_string('certificates', 'theme_echealth'), 'certificates', 'certificate', $children);
            }

            if (file_exists($CFG->dirroot . '/message/index.php')) {
                $this->add_echealth_nav_item($primary, 'communication', new \moodle_url('/message/index.php'), 'message', str_starts_with($path, '/message/'));
            }

            $announcement = $this->get_site_announcement_module();
            if ($announcement) {
                [$cmid, $modcontext] = $announcement;
                if (has_capability('mod/forum:viewdiscussion', $modcontext)) {
                    $this->add_echealth_nav_item(
                        $primary,
                        'announcements',
                        new \moodle_url('/mod/forum/view.php', ['id' => $cmid]),
                        'announcement',
                        $path === '/mod/forum/view.php' && (int)$this->page->url->get_param('id') === $cmid
                    );
                }
            }

            // Course activities in Moodle require a course context. Build the submenu
            // from courses this user can access instead of inventing global destinations.
            $courseitems = [];
            $courses = $enrolledcourses;
            $currentcourseid = $this->page->course->id ?? 0;
            usort($courses, static function($a, $b) use ($currentcourseid) {
                return ((int)$b->id === (int)$currentcourseid) <=> ((int)$a->id === (int)$currentcourseid);
            });
            foreach ($courses as $course) {
                if ((int)$course->id === SITEID) {
                    continue;
                }
                $coursecontext = \context_course::instance($course->id);
                $activities = [];
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $this->add_echealth_nav_item($activities, 'opencourse', $courseurl, 'book-open',
                    $path === '/course/view.php' && (int)$this->page->url->get_param('id') === (int)$course->id);

                if (file_exists($CFG->dirroot . '/mod/assign/index.php') && (
                    has_capability('mod/assign:submit', $coursecontext) || has_capability('mod/assign:grade', $coursecontext)
                )) {
                    $this->add_echealth_nav_item($activities, 'assignments', new \moodle_url('/mod/assign/index.php', ['id' => $course->id]),
                        'clipboard', str_starts_with($path, '/mod/assign/') && (int)$this->page->url->get_param('id') === (int)$course->id);
                }
                if (file_exists($CFG->dirroot . '/mod/quiz/index.php') && (
                    has_capability('mod/quiz:attempt', $coursecontext) || has_capability('mod/quiz:viewreports', $coursecontext)
                )) {
                    $this->add_echealth_nav_item($activities, 'examsquizzes', new \moodle_url('/mod/quiz/index.php', ['id' => $course->id]),
                        'quiz', str_starts_with($path, '/mod/quiz/') && (int)$this->page->url->get_param('id') === (int)$course->id);
                }
                if (!empty($course->showgrades) && file_exists($CFG->dirroot . '/grade/report/user/index.php') &&
                        has_capability('moodle/grade:view', $coursecontext)) {
                    $this->add_echealth_nav_item($activities, 'gradebook',
                        new \moodle_url('/grade/report/user/index.php', ['id' => $course->id]), 'grades',
                        str_starts_with($path, '/grade/report/') && (int)$this->page->url->get_param('id') === (int)$course->id);
                } else if (file_exists($CFG->dirroot . '/grade/report/index.php') &&
                        has_capability('moodle/grade:viewall', $coursecontext)) {
                    $this->add_echealth_nav_item($activities, 'gradebook',
                        new \moodle_url('/grade/report/index.php', ['id' => $course->id]), 'grades',
                        str_starts_with($path, '/grade/report/') && (int)$this->page->url->get_param('id') === (int)$course->id);
                }
                if (!empty($course->enablecompletion) && file_exists($CFG->dirroot . '/report/completion/index.php') &&
                        has_capability('report/completion:view', $coursecontext)) {
                    $this->add_echealth_nav_item($activities, 'completionreport',
                        new \moodle_url('/report/completion/index.php', ['course' => $course->id]), 'completion',
                        $path === '/report/completion/index.php' && (int)$this->page->url->get_param('course') === (int)$course->id);
                }
                if (file_exists($CFG->dirroot . '/question/banks.php') && has_capability('moodle/question:managecategory', $coursecontext)) {
                    $this->add_echealth_nav_item($activities, 'questionbank', new \moodle_url('/question/banks.php', ['courseid' => $course->id]),
                        'file-text', str_starts_with($path, '/question/') && (int)$this->page->url->get_param('courseid') === (int)$course->id);
                }
                if (file_exists($CFG->dirroot . '/enrol/instances.php') && has_capability('moodle/course:enrolreview', $coursecontext)) {
                    $this->add_echealth_nav_item($activities, 'enrolments', new \moodle_url('/enrol/instances.php', ['id' => $course->id]),
                        'enrolment', $path === '/enrol/instances.php' && (int)$this->page->url->get_param('id') === (int)$course->id);
                }
                foreach ($coursemodules[$course->id] ?? [] as $cm) {
                    $forum = $cm->modname === 'forum' ? $DB->get_record('forum', ['id' => $cm->instance], 'id, type', IGNORE_MISSING) : false;
                    if ($forum && $forum->type === 'news' && has_capability('mod/forum:viewdiscussion', \context_module::instance($cm->id))) {
                        $this->add_echealth_nav_item($activities, 'announcements',
                            new \moodle_url('/mod/forum/view.php', ['id' => $cm->id]), 'announcement',
                            $path === '/mod/forum/view.php' && (int)$this->page->url->get_param('id') === (int)$cm->id);
                    }
                }
                if (isset($optionalmodules['attendance'])) {
                    foreach ($coursemodules[$course->id] ?? [] as $cm) {
                        if ($cm->modname === 'attendance' && has_capability('mod/attendance:view', \context_module::instance($cm->id))) {
                            $this->add_echealth_nav_item($activities, 'attendance',
                                new \moodle_url('/mod/attendance/view.php', ['id' => $cm->id]), 'attendance',
                                $path === '/mod/attendance/view.php' && (int)$this->page->url->get_param('id') === (int)$cm->id);
                        }
                    }
                }
                if (count($activities) > 1) {
                $courseitems[] = ['label' => format_string($course->fullname), 'url' => $courseurl->out(false),
                        'icon' => $this->image_url('icons/book', 'theme_echealth')->out(false),
                        'active' => $path === '/course/view.php' && (int)$this->page->url->get_param('id') === (int)$course->id,
                    'children' => $activities];
                }
            }
            if ($courseitems) {
                $teaching[] = ['label' => get_string('courseactivities', 'theme_echealth'), 'url' => '',
                    'icon' => $this->image_url('icons/graduation-cap', 'theme_echealth')->out(false),
                    'active' => false, 'children' => $courseitems];
            }

            if (file_exists($CFG->dirroot . '/' . $CFG->admin . '/index.php') && has_capability('moodle/site:config', $systemcontext)) {
                $adminurl = new \moodle_url('/' . $CFG->admin . '/index.php');
                $this->add_echealth_nav_item($system, 'siteadministration', $adminurl, 'settings',
                    str_starts_with($path, '/' . $CFG->admin . '/'));
                foreach ([
                    ['roles', '/admin/roles/manage.php', 'users'],
                    ['authentication', '/admin/auth.php', 'lock'],
                    ['plugins', '/admin/modules.php', 'settings'],
                    ['scheduledtasks', '/admin/tool/task/scheduledtasks.php', 'clock'],
                ] as [$label, $route, $icon]) {
                    if (file_exists($CFG->dirroot . $route)) {
                        $this->add_echealth_nav_item($system, $label, new \moodle_url($route), $icon, $path === $route);
                    }
                }
                if (!empty($CFG->enablebadges) && file_exists($CFG->dirroot . '/badges/index.php')) {
                    require_once($CFG->libdir . '/badgeslib.php');
                    if (badges_can_manage_badges($systemcontext)) {
                        $this->add_echealth_nav_item($system, 'managebadges',
                            new \moodle_url('/badges/index.php', ['type' => BADGE_TYPE_SITE]), 'award',
                            $path === '/badges/index.php');
                    }
                }
            }
        }

        $supportpage = get_config('core', 'supportpage');
        if (!empty($supportpage) && clean_param($supportpage, PARAM_URL) === $supportpage) {
            $supporturl = new \moodle_url($supportpage);
            $this->add_echealth_nav_item($support, 'helpsupport', $supporturl, 'help', false);
        } else if (!empty($CFG->supportemail) && validate_email($CFG->supportemail)) {
            $supporturl = new \moodle_url('mailto:' . $CFG->supportemail);
            $this->add_echealth_nav_item($support, 'helpsupport', $supporturl, 'help', false);
        }

        $html = \html_writer::start_tag('nav', ['class' => 'ec-navigation', 'aria-label' => get_string('primarynavigation', 'theme_echealth')]);
        $html .= $this->render_echealth_nav_group('learning', $primary);
        if ($teaching) {
            $html .= $this->render_echealth_nav_group('teachingassessments', $teaching, true);
        }
        if ($learners) {
            $html .= $this->render_echealth_nav_group('learnermanagement', $learners, true);
        }
        if ($management) {
            $html .= $this->render_echealth_nav_group('managementreports', $management, true);
        }
        if ($system) {
            $html .= $this->render_echealth_nav_group('administration', $system, true);
        }
        if ($support) {
            $html .= $this->render_echealth_nav_group('support', $support);
        }
        $html .= \html_writer::end_tag('nav');
        $this->echealthnavigation = $html;
        return $this->echealthnavigation;
    }

    /**
     * Add an escaped, labelled navigation item using a Moodle-served SVG asset.
     *
     * @param array $items
     * @param string $labelkey
     * @param \moodle_url $url
     * @param string $icon
     * @param bool $active
     */
    private function add_echealth_nav_item(array &$items, string $labelkey, \moodle_url $url, string $icon, bool $active): void {
        $items[] = [
            'label' => get_string($labelkey, 'theme_echealth'),
            'url' => $url->out(false),
            'icon' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false),
            'active' => $active,
        ];
    }

    /**
     * Render a named navigation group.
     *
     * @param string $titlekey
     * @param array $items
     * @return string
     */
    private function render_echealth_nav_group(string $titlekey, array $items, bool $collapsible = false): string {
        if (!$items) {
            return '';
        }
        if ($collapsible) {
            $html = \html_writer::start_tag('details', ['class' => 'ec-nav-group ec-admin-group', 'data-ec-accordion' => 'group']);
            $html .= \html_writer::tag('summary', get_string($titlekey, 'theme_echealth'), ['class' => 'ec-nav-heading']);
        } else {
            $html = \html_writer::start_tag('section', ['class' => 'ec-nav-group']);
            $html .= \html_writer::tag('h2', get_string($titlekey, 'theme_echealth'), ['class' => 'ec-nav-heading']);
        }
        $html .= \html_writer::start_tag('ul', ['class' => 'ec-nav-list']);
        $hasactive = false;
        foreach ($items as $item) {
            $hasactive = $hasactive || !empty($item['active']) || $this->echealth_nav_children_active($item['children'] ?? []);
            $attributes = [
                'href' => $item['url'],
                'class' => 'ec-nav-link' . ($item['active'] ? ' is-active' : ''),
            ];
            if ($item['active']) {
                $attributes['aria-current'] = 'page';
            }
            $icon = \html_writer::empty_tag('img', [
                'src' => $item['icon'],
                'alt' => '',
                'aria-hidden' => 'true',
                'class' => 'ec-nav-icon',
            ]);
            $content = $icon . \html_writer::tag('span', $item['label'], ['class' => 'ec-nav-label']);
            if (!empty($item['children'])) {
                $html .= \html_writer::start_tag('li', ['class' => 'ec-nav-parent']);
                $html .= \html_writer::start_tag('details', ['class' => 'ec-nav-subgroup', 'data-ec-accordion' => 'submenu',
                    'open' => (!empty($item['active']) || $this->echealth_nav_children_active($item['children'])) ? 'open' : null]);
                $summary = \html_writer::tag('span', $content, ['class' => 'ec-nav-summary-content']);
                $html .= \html_writer::tag('summary', $summary, ['class' => 'ec-nav-link']);
                $html .= $this->render_echealth_nav_items($item['children']);
                $html .= \html_writer::end_tag('details') . \html_writer::end_tag('li');
            } else {
                $html .= \html_writer::tag('li', \html_writer::tag('a', $content, $attributes));
            }
        }
        $html .= \html_writer::end_tag('ul');
        if ($collapsible && $hasactive) {
            // A native details element must have its open attribute at creation.
            $html = str_replace('<details class="ec-nav-group ec-admin-group" data-ec-accordion="group">',
                '<details class="ec-nav-group ec-admin-group" data-ec-accordion="group" open>', $html);
        }
        $html .= \html_writer::end_tag($collapsible ? 'details' : 'section');
        return $html;
    }

    /**
     * Render a nested list of capability-filtered navigation items.
     * @param array $items
     * @return string
     */
    private function render_echealth_nav_items(array $items): string {
        $html = \html_writer::start_tag('ul', ['class' => 'ec-nav-list ec-nav-sublist']);
        foreach ($items as $item) {
            $icon = \html_writer::empty_tag('img', ['src' => $item['icon'], 'alt' => '', 'aria-hidden' => 'true', 'class' => 'ec-nav-icon']);
            $attributes = ['href' => $item['url'], 'class' => 'ec-nav-link' . (!empty($item['active']) ? ' is-active' : '')];
            if (!empty($item['active'])) {
                $attributes['aria-current'] = 'page';
            }
            $content = $icon . \html_writer::tag('span', $item['label'], ['class' => 'ec-nav-label']);
            if (!empty($item['children'])) {
                $html .= \html_writer::start_tag('li', ['class' => 'ec-nav-parent']);
                $html .= \html_writer::start_tag('details', ['class' => 'ec-nav-subgroup', 'data-ec-accordion' => 'submenu',
                    'open' => (!empty($item['active']) || $this->echealth_nav_children_active($item['children'])) ? 'open' : null]);
                $html .= \html_writer::tag('summary', \html_writer::tag('span', $content, ['class' => 'ec-nav-summary-content']), ['class' => 'ec-nav-link']);
                $html .= $this->render_echealth_nav_items($item['children']);
                $html .= \html_writer::end_tag('details') . \html_writer::end_tag('li');
            } else {
                $html .= \html_writer::tag('li', \html_writer::tag('a', $content, $attributes));
            }
        }
        return $html . \html_writer::end_tag('ul');
    }

    /** @param array $items @return bool */
    private function echealth_nav_children_active(array $items): bool {
        foreach ($items as $item) {
            if (!empty($item['active']) || $this->echealth_nav_children_active($item['children'] ?? [])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find the site news forum module, if one is configured.
     *
     * @return array|null
     */
    private function get_site_announcement_module(): ?array {
        global $DB;
        static $resolved = false;
        static $module = null;
        if ($resolved) {
            return $module;
        }
        $resolved = true;

        $modules = \core_component::get_plugin_list('mod');
        if (!isset($modules['forum'])) {
            return null;
        }
        $forum = $DB->get_record('forum', ['course' => SITEID, 'type' => 'news'], 'id', IGNORE_MISSING);
        if (!$forum) {
            return null;
        }
        $cm = get_coursemodule_from_instance('forum', $forum->id, SITEID, false, IGNORE_MISSING);
        if (!$cm || empty($cm->visible)) {
            return null;
        }
        $module = [$cm->id, \context_module::instance($cm->id)];
        return $module;
    }

    /**
     * Add a data-backed learner dashboard panel above the user's normal Moodle blocks.
     *
     * @return string
     */
    public function full_header() {
        $header = parent::full_header();
        if (!$this->is_echealth_personal_dashboard()) {
            return $header;
        }
        return $header . $this->echealth_dashboard_panel();
    }

    /**
     * Determine whether the current page is the personal dashboard rather than My Courses.
     *
     * @return bool
     */
    private function is_echealth_personal_dashboard(): bool {
        $path = $this->page->url->get_path();
        return isloggedin() && !isguestuser() && $this->page->pagetype === 'my-index' &&
            in_array($path, ['/my/', '/my/index.php'], true);
    }

    /**
     * Build a small dashboard from the current user's actual enrolments, course completion,
     * calendar events, and site announcements.
     *
     * @return string
     */
    private function echealth_dashboard_panel(): string {
        global $CFG, $DB, $USER;

        $courses = enrol_get_my_courses(
            'id, fullname, shortname, summary, category, startdate, enddate, enablecompletion, showgrades, cacherev',
            'sortorder ASC'
        );
        $coursecount = count($courses);
        $dashboardrole = $this->echealth_dashboard_role($courses);
        $completioncourseids = [];
        foreach ($courses as $course) {
            if (!empty($course->enablecompletion)) {
                $completioncourseids[] = (int)$course->id;
            }
        }
        $completedcount = 0;
        if ($completioncourseids) {
            [$completioncoursesql, $completionparams] = $DB->get_in_or_equal(
                $completioncourseids, SQL_PARAMS_NAMED, 'completioncourse');
            $completionparams['userid'] = (int)$USER->id;
            $completedcount = $DB->count_records_select(
                'course_completions',
                'userid = :userid AND timecompleted > 0 AND course ' . $completioncoursesql,
                $completionparams
            );
        }
        $pendinggrading = $this->echealth_pending_grading_count(array_slice($courses, 0, 30));
        $badges = $this->echealth_user_badges();
        $dashboardcourses = $dashboardrole === 'lecturer'
            ? array_values(array_filter($courses, function($course) {
                $context = \context_course::instance($course->id);
                return has_capability('moodle/course:update', $context) || has_capability('mod/assign:grade', $context);
            })) : $courses;
        $staffdashboard = $dashboardrole === 'lecturer';
        $coursecards = $this->echealth_course_cards(array_slice($dashboardcourses, 0, 6), !$staffdashboard, !$staffdashboard);
        $cataloguecards = $coursecards ? [] : $this->echealth_course_cards($this->echealth_catalogue_courses(), false);
        $upcoming = $this->echealth_upcoming_events($courses);
        $announcements = $this->echealth_announcements($courses);
        $notifications = $this->echealth_notifications();

        $html = \html_writer::start_tag('section', [
            'class' => 'ec-dashboard-panel',
            'aria-labelledby' => 'ec-dashboard-welcome',
        ]);
        $html .= \html_writer::tag('p', get_string('dashboardeyebrow', 'theme_echealth'), ['class' => 'ec-dashboard-eyebrow']);
        $html .= \html_writer::tag('h2', get_string('welcomeuser', 'theme_echealth', fullname($USER)), ['id' => 'ec-dashboard-welcome']);
        $html .= \html_writer::tag('p', get_string('dashboardtagline', 'theme_echealth'), ['class' => 'ec-dashboard-tagline']);
        $introkey = 'dashboardintrostudent';
        if ($dashboardrole === 'administrator') {
            $introkey = 'dashboardintroadmin';
        } else if ($dashboardrole === 'lecturer') {
            $introkey = 'dashboardintroteacher';
        } else if ($dashboardrole === 'coordinator') {
            $introkey = 'dashboardintrocoordinator';
        }
        $html .= \html_writer::tag('p', get_string($introkey, 'theme_echealth'), ['class' => 'ec-dashboard-intro']);
        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-stats', 'aria-label' => get_string('learningoverview', 'theme_echealth')]);
        if ($dashboardrole === 'administrator') {
            $activeusers = $DB->count_records_select('user', 'deleted = 0 AND suspended = 0 AND id > 1');
            $visiblecourses = $DB->count_records_select('course', 'id <> :siteid AND visible = 1', ['siteid' => SITEID]);
            $html .= $this->echealth_stat_card('users', 'activelearners', (string)$activeusers);
            $html .= $this->echealth_stat_card('book-open', 'availablecourses', (string)$visiblecourses);
        } else if ($dashboardrole === 'lecturer') {
            $teachingcount = $this->echealth_teaching_course_count($courses);
            $html .= $this->echealth_stat_card('graduation-cap', 'teachingcourses', (string)$teachingcount);
            if ($pendinggrading !== null) {
                $html .= $this->echealth_stat_card('clipboard', 'pendinggrading', (string)$pendinggrading);
            }
        } else {
            $html .= $this->echealth_stat_card('book-open', 'enrolledcourses', (string)$coursecount);
            $html .= $this->echealth_stat_card('check-circle', 'completedcourses', (string)$completedcount);
        }
        if ($badges !== null) {
            $html .= $this->echealth_stat_card('award', 'earnedbadges', (string)count($badges));
        }
        if ($dashboardrole !== 'administrator' && $dashboardrole !== 'lecturer' && $pendinggrading !== null) {
            $html .= $this->echealth_stat_card('clipboard', 'pendinggrading', (string)$pendinggrading);
        }
        $html .= \html_writer::end_tag('div');

        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-columns']);
        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-main']);
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-courses-heading']);
        $courseheading = $dashboardrole === 'lecturer' && $coursecards ? 'teachingcourses' :
            ($coursecards ? 'yourcourses' : 'explorecourses');
        $html .= \html_writer::tag('h3', get_string($courseheading, 'theme_echealth'), ['id' => 'ec-courses-heading']);
        if ($coursecards) {
            $html .= \html_writer::tag('div', implode('', $coursecards), ['class' => 'ec-course-grid']);
        } else if ($cataloguecards) {
            $html .= \html_writer::tag('div', implode('', $cataloguecards), ['class' => 'ec-course-grid']);
            $html .= \html_writer::link(new \moodle_url('/course/index.php'), get_string('viewallcourses', 'theme_echealth'),
                ['class' => 'ec-inline-action ec-view-all-courses']);
        } else {
            $html .= $this->echealth_empty_state('book-open', 'nocourses', 'browsecatalogue', new \moodle_url('/course/index.php'));
        }
        $html .= \html_writer::end_tag('section');
        $gradebook = $this->echealth_gradebook_summary($courses);
        if ($gradebook !== '') {
            $html .= $gradebook;
        }
        if ($badges !== null) {
            $html .= $this->echealth_badge_panel($badges);
        }
        $html .= \html_writer::end_tag('div');

        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-aside']);
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-upcoming-heading']);
        $html .= \html_writer::tag('h3', get_string('upcomingactivities', 'theme_echealth'), ['id' => 'ec-upcoming-heading']);
        $html .= $upcoming ?: $this->echealth_empty_state('calendar', 'noupcomingactivities', 'viewcalendar', new \moodle_url('/calendar/view.php'));
        $html .= \html_writer::end_tag('section');
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-announcements-heading']);
        $html .= \html_writer::tag('h3', get_string('announcements', 'theme_echealth'), ['id' => 'ec-announcements-heading']);
        $announcementurl = $this->site_announcements_url($courses);
        $html .= $announcements ?: $this->echealth_empty_state('announcement', 'noannouncements',
            $announcementurl ? 'viewannouncements' : null, $announcementurl);
        $html .= \html_writer::end_tag('section');
        $html .= $notifications;
        $html .= \html_writer::end_tag('div');
        $html .= \html_writer::end_tag('div');
        $html .= $this->echealth_quick_actions();
        $html .= \html_writer::end_tag('section');
        return $html;
    }

    /**
     * Load the current user's earned local Moodle badges when the feature is enabled.
     *
     * @return array|null Null when badges are unavailable to this account; otherwise issued badges.
     */
    private function echealth_user_badges(): ?array {
        global $CFG, $USER;

        if (empty($CFG->enablebadges) || !file_exists($CFG->dirroot . '/badges/mybadges.php') ||
                !has_capability('moodle/badges:manageownbadges', \context_user::instance($USER->id))) {
            return null;
        }
        require_once($CFG->libdir . '/badgeslib.php');
        return badges_get_user_badges((int)$USER->id);
    }

    /**
     * Render visible own course final grades using Moodle's gradebook API and permissions.
     *
     * @param array $courses Accessible enrolled courses.
     * @return string
     */
    private function echealth_gradebook_summary(array $courses): string {
        global $CFG, $USER;

        if (!file_exists($CFG->dirroot . '/grade/report/user/index.php')) {
            return '';
        }
        require_once($CFG->libdir . '/gradelib.php');
        $items = [];
        foreach (array_slice($courses, 0, 6) as $course) {
            $context = \context_course::instance($course->id);
            if (empty($course->showgrades) || !has_capability('moodle/grade:view', $context) ||
                    !has_capability('gradereport/user:view', $context)) {
                continue;
            }
            $gradeitem = \grade_item::fetch_course_item($course->id);
            if (!$gradeitem || ($gradeitem->is_hidden() && !has_capability('moodle/grade:viewhidden', $context))) {
                continue;
            }
            $grade = $gradeitem->get_grade((int)$USER->id, false);
            if (!$grade) {
                $gradevalue = get_string('gradepending', 'theme_echealth');
            } else if ($grade->is_hidden() && !has_capability('moodle/grade:viewhidden', $context)) {
                continue;
            } else if ($grade->finalgrade === null) {
                $gradevalue = get_string('gradepending', 'theme_echealth');
            } else {
                $gradevalue = grade_format_gradevalue($grade->finalgrade, $gradeitem);
            }
            $url = new \moodle_url('/grade/report/user/index.php', ['id' => $course->id]);
            $gradehtml = \html_writer::tag('span', $gradevalue, ['class' => 'ec-grade-value']);
            $items[] = \html_writer::tag('li',
                \html_writer::link($url, format_string($course->fullname), ['class' => 'ec-grade-course']) . $gradehtml);
        }
        if (!$items) {
            return '';
        }
        $html = \html_writer::start_tag('section', ['class' => 'ec-dashboard-section ec-gradebook-panel',
            'aria-labelledby' => 'ec-gradebook-heading']);
        $html .= \html_writer::tag('h3', get_string('gradebooksummary', 'theme_echealth'), ['id' => 'ec-gradebook-heading']);
        $html .= \html_writer::tag('ul', implode('', $items), ['class' => 'ec-gradebook-list']);
        return $html . \html_writer::end_tag('section');
    }

    /**
     * Render the user's most recent issued badges without inventing awards.
     *
     * @param array $badges Badges returned by Moodle's badge library.
     * @return string
     */
    private function echealth_badge_panel(array $badges): string {
        global $CFG;

        $html = \html_writer::start_tag('section', ['class' => 'ec-dashboard-section ec-badges-panel',
            'aria-labelledby' => 'ec-badges-heading']);
        $html .= \html_writer::tag('h3', get_string('mybadges', 'theme_echealth'), ['id' => 'ec-badges-heading']);
        if (!$badges) {
            $html .= \html_writer::div($this->echealth_icon('award', 'ec-empty-icon') .
                \html_writer::tag('p', get_string('nobadgesearned', 'theme_echealth')), 'ec-empty-state');
        } else {
            $items = [];
            $fs = get_file_storage();
            foreach (array_slice($badges, 0, 3) as $badge) {
                $context = $badge->type == BADGE_TYPE_SITE ? \context_system::instance() :
                    \context_course::instance($badge->courseid);
                $image = $fs->get_file($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f3.png');
                $imageurl = $image && !$image->is_directory()
                    ? \moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f3')
                    : $this->image_url('icons/award', 'theme_echealth');
                $icon = \html_writer::empty_tag('img', ['src' => $imageurl->out(false), 'alt' => '',
                    'aria-hidden' => 'true', 'class' => 'ec-badge-image']);
                $url = new \moodle_url('/badges/badge.php', ['hash' => $badge->uniquehash]);
                $title = \html_writer::link($url, format_string($badge->name), ['class' => 'ec-badge-title']);
                $date = \html_writer::tag('span', userdate($badge->dateissued, get_string('strftimedate', 'langconfig')),
                    ['class' => 'ec-badge-date']);
                $items[] = \html_writer::tag('li', $icon . $title . $date);
            }
            $html .= \html_writer::tag('ul', implode('', $items), ['class' => 'ec-badge-list']);
        }
        if (file_exists($CFG->dirroot . '/badges/mybadges.php')) {
            $html .= \html_writer::link(new \moodle_url('/badges/mybadges.php'), get_string('viewallbadges', 'theme_echealth'),
                ['class' => 'ec-inline-action']);
        }
        return $html . \html_writer::end_tag('section');
    }

    /**
     * Resolve a dashboard view from capabilities, allowing a learner who also teaches to see staff tools.
     *
     * @param array $courses Courses the current account can access.
     * @return string student, lecturer, coordinator or administrator.
     */
    private function echealth_dashboard_role(array $courses): string {
        $systemcontext = \context_system::instance();
        if (has_capability('moodle/site:config', $systemcontext)) {
            return 'administrator';
        }
        $isteacher = false;
        $iscoordinator = has_capability('moodle/cohort:manage', $systemcontext);
        foreach ($courses as $course) {
            $coursecontext = \context_course::instance($course->id);
            $categorycontext = \context_coursecat::instance($course->category);
            if (has_capability('moodle/category:manage', $categorycontext) ||
                    has_capability('moodle/site:viewreports', $coursecontext)) {
                $iscoordinator = true;
            }
            if (has_capability('moodle/course:update', $coursecontext) ||
                    has_capability('mod/assign:grade', $coursecontext)) {
                $isteacher = true;
            }
        }
        if ($isteacher) {
            return 'lecturer';
        }
        if ($iscoordinator) {
            return 'coordinator';
        }
        return 'student';
    }

    /**
     * Count courses where the user can teach or grade.
     *
     * @param array $courses Courses the current account can access.
     * @return int
     */
    private function echealth_teaching_course_count(array $courses): int {
        $count = 0;
        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            if (has_capability('moodle/course:update', $context) || has_capability('mod/assign:grade', $context)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Render useful dashboard destinations only when the current user can use them.
     *
     * @return string
     */
    private function echealth_quick_actions(): string {
        global $CFG;

        $context = \context_system::instance();
        $actions = [];
        $this->add_echealth_quick_action($actions, 'catalogue', new \moodle_url('/course/index.php'), 'catalogue');
        $this->add_echealth_quick_action($actions, 'calendar', new \moodle_url('/calendar/view.php'), 'calendar');
        $createcategoryid = $this->echealth_course_creation_category();
        if (file_exists($CFG->dirroot . '/course/edit.php') && $createcategoryid !== null) {
            $this->add_echealth_quick_action($actions, 'createcourse', new \moodle_url('/course/edit.php', ['category' => $createcategoryid]), 'plus');
        }
        if (file_exists($CFG->dirroot . '/admin/user.php') && (
                has_capability('moodle/user:create', $context) || has_capability('moodle/user:update', $context))) {
            $this->add_echealth_quick_action($actions, 'users', new \moodle_url('/admin/user.php'), 'users');
        }
        if (file_exists($CFG->dirroot . '/cohort/index.php') && has_capability('moodle/cohort:manage', $context)) {
            $this->add_echealth_quick_action($actions, 'cohorts', new \moodle_url('/cohort/index.php'), 'campus');
        }
        if (file_exists($CFG->dirroot . '/reportbuilder/index.php') && has_capability('moodle/site:config', $context)) {
            $this->add_echealth_quick_action($actions, 'reports', new \moodle_url('/reportbuilder/index.php'), 'reports');
        }
        if (file_exists($CFG->dirroot . '/mod/assign/index.php')) {
            foreach (enrol_get_my_courses('id, fullname', 'sortorder ASC', 30) as $course) {
                if (has_capability('mod/assign:grade', \context_course::instance($course->id))) {
                    $this->add_echealth_quick_action($actions, 'reviewgrading',
                        new \moodle_url('/mod/assign/index.php', ['id' => $course->id]), 'clipboard');
                    break;
                }
            }
        }

        $html = \html_writer::start_tag('section', ['class' => 'ec-quick-actions', 'aria-labelledby' => 'ec-quick-actions-heading']);
        $html .= \html_writer::tag('h3', get_string('quickactions', 'theme_echealth'), ['id' => 'ec-quick-actions-heading']);
        $html .= \html_writer::tag('div', implode('', $actions), ['class' => 'ec-quick-action-grid']);
        $html .= \html_writer::end_tag('section');
        return $html;
    }

    /**
     * Add an icon-and-label dashboard action.
     *
     * @param array $actions
     * @param string $labelkey
     * @param \moodle_url $url
     * @param string $icon
     */
    private function add_echealth_quick_action(array &$actions, string $labelkey, \moodle_url $url, string $icon): void {
        $iconhtml = \html_writer::empty_tag('img', [
            'src' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false),
            'alt' => '',
            'aria-hidden' => 'true',
            'class' => 'ec-quick-action-icon',
        ]);
        $actions[] = \html_writer::link($url, $iconhtml . \html_writer::tag('span', get_string($labelkey, 'theme_echealth')),
            ['class' => 'ec-quick-action']);
    }

    /**
     * Check course-management capabilities at system or visible category scope.
     *
     * @return bool
     */
    private function echealth_can_manage_courses(): bool {
        if ($this->echealthcanmanagecourses !== null) {
            return $this->echealthcanmanagecourses;
        }
        $systemcontext = \context_system::instance();
        if (has_capability('moodle/course:create', $systemcontext) ||
                has_capability('moodle/category:manage', $systemcontext)) {
            $this->echealthcanmanagecourses = true;
            return $this->echealthcanmanagecourses;
        }
        foreach (\core_course_category::get_all(['returnhidden' => true]) as $category) {
            $categorycontext = $category->get_context();
            if (has_capability('moodle/course:create', $categorycontext) ||
                    has_capability('moodle/category:manage', $categorycontext)) {
                $this->echealthcanmanagecourses = true;
                return $this->echealthcanmanagecourses;
            }
        }
        $this->echealthcanmanagecourses = false;
        return $this->echealthcanmanagecourses;
    }

    /**
     * Return a course category where the current user can create courses.
     *
     * @return int|null
     */
    private function echealth_course_creation_category(): ?int {
        $defaultcategory = \core_course_category::get_default();
        if (has_capability('moodle/course:create', $defaultcategory->get_context())) {
            return (int)$defaultcategory->id;
        }
        foreach (\core_course_category::get_all(['returnhidden' => true]) as $category) {
            if (has_capability('moodle/course:create', $category->get_context())) {
                return (int)$category->id;
            }
        }
        return null;
    }

    /**
     * Render two data-backed dashboard figures.
     *
     * @param string $icon
     * @param string $labelkey
     * @param string $value
     * @return string
     */
    private function echealth_stat_card(string $icon, string $labelkey, string $value): string {
        $iconhtml = \html_writer::empty_tag('img', [
            'src' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false),
            'alt' => '',
            'aria-hidden' => 'true',
            'class' => 'ec-stat-icon',
        ]);
        $content = $iconhtml . \html_writer::tag('span', $value, ['class' => 'ec-stat-value']) .
            \html_writer::tag('span', get_string($labelkey, 'theme_echealth'), ['class' => 'ec-stat-label']);
        return \html_writer::tag('div', $content, ['class' => 'ec-stat-card']);
    }

    /**
     * Render the user's first six enrolled courses with their real category, course image,
     * and Moodle's standard completion percentage API.
     *
     * @param array $courses
     * @return array
     */
    private function echealth_course_cards(array $courses, bool $showprogress = true, bool $iscatalogue = true): array {
        $categories = [];
        foreach ($courses as $course) {
            $categories[$course->category] = true;
        }
        $categorynames = [];
        if ($categories) {
            $categoryrecords = \core_course_category::get_many(array_keys($categories));
            foreach ($categoryrecords as $category) {
                $categorynames[$category->id] = $category->name;
            }
        }

        $cards = [];
        foreach ($courses as $course) {
            $courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
            if (!$courseimage) {
                $sampleimage = $this->echealth_sample_course_image($course->fullname);
                $courseimage = $sampleimage
                    ? (new \moodle_url('/theme/echealth/pix/course/' . $sampleimage))->out(false)
                    : $this->image_url('graphics/course-placeholder', 'theme_echealth')->out(false);
            }

            $categoryname = $categorynames[$course->category] ?? get_string('uncategorised', 'theme_echealth');
            $progress = null;
            if ($showprogress && !empty($course->enablecompletion)) {
                $progress = \core_completion\progress::get_course_progress_percentage($course);
            }
            $categoryicon = $this->echealth_icon('book', 'ec-course-meta-icon');
            $meta = \html_writer::tag('span', $categoryicon . format_string($categoryname), ['class' => 'ec-course-category']);
            if ($progress !== null) {
                $progressvalue = (int)floor($progress);
                $meta .= \html_writer::tag('div',
                    \html_writer::tag('span', '', ['class' => 'ec-progress-value', 'style' => 'width:' . $progressvalue . '%']),
                    ['class' => 'ec-progress', 'role' => 'progressbar', 'aria-valuenow' => $progressvalue,
                        'aria-valuemin' => 0, 'aria-valuemax' => 100, 'aria-valuetext' => get_string('progress', 'theme_echealth', $progressvalue),
                        'aria-label' => get_string('courseprogress', 'theme_echealth', format_string($course->fullname))]
                );
            } else if ($showprogress) {
                $meta .= \html_writer::tag('span', get_string('progressnottracked', 'theme_echealth'), ['class' => 'ec-progress-note']);
            } else {
                $label = $iscatalogue ? 'cataloguecourse' : 'teachingcourse';
                $meta .= \html_writer::tag('span', get_string($label, 'theme_echealth'), ['class' => 'ec-progress-note']);
            }
            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $image = \html_writer::empty_tag('img', [
                'src' => $courseimage,
                'alt' => '',
                'aria-hidden' => 'true',
                'class' => 'ec-course-image',
                'loading' => 'lazy',
            ]);
            $link = \html_writer::link($courseurl, format_string($course->fullname), ['class' => 'ec-course-title']);
            $footer = \html_writer::link($courseurl,
                get_string('opencourse', 'theme_echealth') . $this->echealth_icon('arrow-right', 'ec-course-action-icon'),
                ['class' => 'ec-course-action']);
            $cards[] = \html_writer::tag('article', $image .
                \html_writer::div($meta . $link . $footer, 'ec-course-card-content'), ['class' => 'ec-course-card']);
        }
        return $cards;
    }

    /**
     * Return up to six visible catalogue courses, one card per distinct course title.
     * These are discovery links, not enrolments or personal progress.
     *
     * @return array
     */
    private function echealth_catalogue_courses(): array {
        $courses = get_courses('all', 'c.sortorder ASC',
            'c.id, c.fullname, c.shortname, c.summary, c.category, c.startdate, c.enddate, c.enablecompletion, c.cacherev');
        $selected = [];
        $seen = [];
        foreach ($courses as $course) {
            if ((int)$course->id === SITEID) {
                continue;
            }
            $key = \core_text::strtolower(trim($course->fullname));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $selected[] = $course;
            if (count($selected) >= 6) {
                break;
            }
        }
        return $selected;
    }

    /**
     * Count real assignment submissions awaiting grading in courses this user grades.
     * Returns null for users without grading permissions, so learners don't see an irrelevant metric.
     *
     * @param array $courses
     * @return int|null
     */
    private function echealth_pending_grading_count(array $courses): ?int {
        global $CFG;

        $hasgradingpermission = false;
        $pending = 0;
        $loadedassignlibrary = false;
        foreach ($courses as $course) {
            $coursecontext = \context_course::instance($course->id);
            if (!has_capability('mod/assign:grade', $coursecontext)) {
                continue;
            }
            $hasgradingpermission = true;
            $modinfo = get_fast_modinfo($course);
            foreach ($modinfo->get_instances_of('assign') as $cm) {
                if (!$cm->uservisible || !has_capability('mod/assign:grade', \context_module::instance($cm->id))) {
                    continue;
                }
                if (!$loadedassignlibrary) {
                    require_once($CFG->dirroot . '/mod/assign/locallib.php');
                    $loadedassignlibrary = true;
                }
                $assignment = new \assign(\context_module::instance($cm->id), $cm, $course);
                $pending += $assignment->count_submissions_need_grading();
            }
        }
        return $hasgradingpermission ? $pending : null;
    }

    /**
     * Match only the six sample course names included in the supplied kit.
     *
     * @param string $fullname
     * @return string|null
     */
    private function echealth_sample_course_image(string $fullname): ?string {
        $name = \core_text::strtolower($fullname);
        $samples = [
            'infection prevention' => 'infection-prevention.webp',
            'foundations of nursing' => 'fundamentals-nursing.webp',
            'fundamentals of nursing' => 'fundamentals-nursing.webp',
            'clinical skills' => 'clinical-skills.webp',
            'maternal and child health' => 'maternal-child-health.webp',
            'emergency nursing' => 'emergency-nursing.webp',
            'healthcare ethics' => 'healthcare-ethics.webp',
        ];
        foreach ($samples as $needle => $image) {
            if (str_contains($name, $needle)) {
                return $image;
            }
        }
        return null;
    }

    /**
     * Render events from the current user's calendar and enrolled courses.
     *
     * @param array $courses
     * @return string
     */
    private function echealth_upcoming_events(array $courses): string {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/calendar/lib.php');
        $courseids = [SITEID];
        $groupids = [];
        foreach ($courses as $course) {
            $courseids[] = (int)$course->id;
            foreach (groups_get_user_groups((int)$course->id, (int)$USER->id) as $groups) {
                foreach ($groups as $groupid) {
                    $groupids[] = (int)$groupid;
                }
            }
        }
        $courseids = array_values(array_unique($courseids));
        $groupids = array_values(array_unique($groupids));
        $events = calendar_get_events(time(), time() + (30 * DAYSECS), (int)$USER->id,
            $groupids ?: false, $courseids, true, true);
        if (!$events) {
            return '';
        }
        $items = [];
        foreach (array_slice($events, 0, 5) as $event) {
            $date = userdate($event->timestart, get_string('strftimedate', 'langconfig'));
            $url = new \moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $event->timestart]);
            $items[] = \html_writer::tag('li',
                \html_writer::tag('span', $this->echealth_icon('clock', 'ec-inline-icon') . $date, ['class' => 'ec-event-date']) .
                \html_writer::link($url, format_string($event->name), ['class' => 'ec-event-title']));
        }
        return \html_writer::tag('ul', implode('', $items), ['class' => 'ec-event-list']);
    }

    /**
     * Render recent items from the real site news forum, when one is configured and accessible.
     *
     * @return string
     */
    private function echealth_announcements(array $courses): string {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/forum/lib.php');
        $forums = [];
        $siteannouncement = $this->get_site_announcement_module();
        if ($siteannouncement) {
            $forums[] = [$siteannouncement[0], SITEID];
        }
        foreach (array_slice($courses, 0, 30) as $course) {
            foreach (get_fast_modinfo($course)->get_instances_of('forum') as $cm) {
                if (!$cm->uservisible || !has_capability('mod/forum:viewdiscussion', \context_module::instance($cm->id))) {
                    continue;
                }
                $forum = $DB->get_record('forum', ['id' => $cm->instance], 'id, type', IGNORE_MISSING);
                if ($forum && $forum->type === 'news') {
                    $forums[] = [$cm->id, (int)$course->id];
                }
            }
        }
        if (!$forums) {
            return '';
        }
        $discussions = [];
        foreach ($forums as [$cmid, $courseid]) {
            $cm = get_coursemodule_from_id('forum', $cmid, $courseid, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            foreach (forum_get_discussions($cm, 'p.modified DESC', true, -1, 5) as $discussion) {
                $discussion->ec_courseid = $courseid;
                $discussions[] = $discussion;
            }
        }
        usort($discussions, static fn($a, $b) => (int)$b->timemodified <=> (int)$a->timemodified);
        $discussions = array_slice($discussions, 0, 4);
        if (!$discussions) {
            return '';
        }
        $items = [];
        foreach ($discussions as $discussion) {
            $url = new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->discussion]);
            $items[] = \html_writer::tag('li', \html_writer::link($url,
                $this->echealth_icon('announcement', 'ec-inline-icon') . format_string($discussion->name) .
                \html_writer::tag('span', userdate($discussion->timemodified, get_string('strftimedate', 'langconfig')),
                    ['class' => 'ec-announcement-date'])));
        }
        return \html_writer::tag('ul', implode('', $items), ['class' => 'ec-announcement-list']);
    }

    /**
     * Render the user's unread Moodle notifications and native notification controls.
     *
     * @return string
     */
    private function echealth_notifications(): string {
        global $USER;

        if (!\core_message\api::is_processor_enabled('popup')) {
            return '';
        }
        $count = \message_popup\api::count_unread_popup_notifications((int)$USER->id);
        $html = \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-notifications-heading']);
        $html .= \html_writer::tag('h3', get_string('notifications', 'message'), ['id' => 'ec-notifications-heading']);
        $message = get_string($count ? 'unreadnotifications' : 'nounreadnotifications', 'theme_echealth', $count);
        $html .= \html_writer::tag('p', $this->echealth_icon('bell', 'ec-inline-icon') . $message, ['class' => 'ec-notification-count']);
        $html .= \html_writer::link(new \moodle_url('/message/output/popup/notifications.php'),
            get_string('viewnotifications', 'theme_echealth'), ['class' => 'ec-inline-action']);
        if (has_capability('moodle/user:editownmessageprofile', \context_system::instance())) {
            $html .= ' ' . \html_writer::link(new \moodle_url('/message/notificationpreferences.php'),
                get_string('notificationpreferences', 'message'), ['class' => 'ec-inline-action']);
        }
        return $html . \html_writer::end_tag('section');
    }

    /**
     * Return the site announcements destination or the front page when no news forum exists.
     *
     * @return \moodle_url
     */
    private function site_announcements_url(array $courses): ?\moodle_url {
        global $DB;

        $module = $this->get_site_announcement_module();
        if ($module) {
            return new \moodle_url('/mod/forum/view.php', ['id' => $module[0]]);
        }
        foreach ($courses as $course) {
            foreach (get_fast_modinfo($course)->get_instances_of('forum') as $cm) {
                if (!$cm->uservisible || !has_capability('mod/forum:viewdiscussion', \context_module::instance($cm->id))) {
                    continue;
                }
                $forum = $DB->get_record('forum', ['id' => $cm->instance], 'id, type', IGNORE_MISSING);
                if ($forum && $forum->type === 'news') {
                    return new \moodle_url('/mod/forum/view.php', ['id' => $cm->id]);
                }
            }
        }
        return null;
    }

    /**
     * Render a truthful empty state with an optional working action.
     *
     * @param string $icon
     * @param string $messagekey
     * @param string $actionkey
     * @param \moodle_url $url
     * @return string
     */
    private function echealth_empty_state(string $icon, string $messagekey, ?string $actionkey, ?\moodle_url $url): string {
        $iconhtml = $this->echealth_icon($icon, 'ec-empty-icon');
        $html = $iconhtml . \html_writer::tag('p', get_string($messagekey, 'theme_echealth'));
        if ($actionkey && $url) {
            $html .= \html_writer::link($url, get_string($actionkey, 'theme_echealth'), ['class' => 'ec-inline-action']);
        }
        return \html_writer::div($html, 'ec-empty-state');
    }

    /**
     * Return a decorative, Moodle-served kit icon.
     *
     * @param string $icon
     * @param string $class
     * @return string
     */
    private function echealth_icon(string $icon, string $class): string {
        return \html_writer::empty_tag('img', [
            'src' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false),
            'alt' => '',
            'aria-hidden' => 'true',
            'class' => $class,
        ]);
    }
}
