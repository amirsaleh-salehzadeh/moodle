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
        return format_string(get_config('theme_echealth', 'departmentname') ?: get_string('departmentname', 'theme_echealth'));
    }

    /**
     * Return the configurable portal name.
     *
     * @return string
     */
    public function echealth_portal_name(): string {
        return format_string(get_config('theme_echealth', 'portalname') ?: get_string('portalname', 'theme_echealth'));
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
        $administration = [];
        $support = [];

        if (!isloggedin() || isguestuser()) {
            $this->add_echealth_nav_item($primary, 'sitehome', new \moodle_url('/'), 'home', $path === '/');
            $this->add_echealth_nav_item($primary, 'catalogue', new \moodle_url('/course/index.php'), 'catalogue', $path === '/course/index.php');
        } else {
            if (!isset($CFG->enabledashboard) || $CFG->enabledashboard) {
                $this->add_echealth_nav_item($primary, 'dashboard', new \moodle_url('/my/'), 'dashboard', in_array($path, ['/my/', '/my/index.php'], true));
            }
            if (!empty($CFG->enablemycourses) && file_exists($CFG->dirroot . '/my/courses.php')) {
                $this->add_echealth_nav_item($primary, 'mycourses', new \moodle_url('/my/courses.php'), 'courses', $path === '/my/courses.php');
            }
            $this->add_echealth_nav_item($primary, 'sitehome', new \moodle_url('/'), 'home', $path === '/');
            $this->add_echealth_nav_item($primary, 'catalogue', new \moodle_url('/course/index.php'), 'catalogue', $path === '/course/index.php');
            $this->add_echealth_nav_item($primary, 'calendar', new \moodle_url('/calendar/view.php'), 'calendar', $path === '/calendar/view.php');

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

            if (file_exists($CFG->dirroot . '/admin/user.php') && (
                has_capability('moodle/user:create', $systemcontext) ||
                has_capability('moodle/user:update', $systemcontext)
            )) {
                $this->add_echealth_nav_item($administration, 'users', new \moodle_url('/admin/user.php'), 'users', $path === '/admin/user.php');
            }
            if (file_exists($CFG->dirroot . '/course/management.php') && (
                has_capability('moodle/course:create', $systemcontext) ||
                has_capability('moodle/category:manage', $systemcontext)
            )) {
                $this->add_echealth_nav_item($administration, 'coursesadmin', new \moodle_url('/course/management.php'), 'book', $path === '/course/management.php');
            }
            if (file_exists($CFG->dirroot . '/cohort/index.php') &&
                    has_capability('moodle/cohort:manage', $systemcontext)) {
                $this->add_echealth_nav_item($administration, 'cohorts', new \moodle_url('/cohort/index.php'), 'campus', $path === '/cohort/index.php');
            }
            if (file_exists($CFG->dirroot . '/admin/tool/lp/competencies.php') &&
                    has_capability('tool/lp:competencymanage', $systemcontext)) {
                $this->add_echealth_nav_item(
                    $administration,
                    'competencies',
                    new \moodle_url('/admin/tool/lp/competencies.php'),
                    'competency',
                    str_starts_with($path, '/admin/tool/lp/')
                );
            }
            if (file_exists($CFG->dirroot . '/reportbuilder/index.php') &&
                    has_capability('moodle/site:config', $systemcontext)) {
                $this->add_echealth_nav_item($administration, 'reports', new \moodle_url('/reportbuilder/index.php'), 'reports', str_starts_with($path, '/reportbuilder/'));
            } else if (file_exists($CFG->dirroot . '/report/log/index.php')) {
                foreach (enrol_get_my_courses('id', 'sortorder ASC', 6) as $course) {
                    if (has_capability('moodle/site:viewreports', \context_course::instance($course->id))) {
                        $this->add_echealth_nav_item(
                            $administration,
                            'reports',
                            new \moodle_url('/report/log/index.php', ['id' => $course->id]),
                            'reports',
                            $path === '/report/log/index.php' && (int)$this->page->url->get_param('id') === (int)$course->id
                        );
                        break;
                    }
                }
            }
            if (file_exists($CFG->dirroot . '/' . $CFG->admin . '/index.php') &&
                    has_capability('moodle/site:config', $systemcontext)) {
                $this->add_echealth_nav_item(
                    $administration,
                    'siteadministration',
                    new \moodle_url('/' . $CFG->admin . '/index.php'),
                    'settings',
                    str_starts_with($path, '/' . $CFG->admin . '/')
                );
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
        if ($administration) {
            $html .= $this->render_echealth_nav_group('administration', $administration, true);
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
            $html = \html_writer::start_tag('details', ['class' => 'ec-nav-group ec-admin-group']);
            $html .= \html_writer::tag('summary', get_string($titlekey, 'theme_echealth'), ['class' => 'ec-nav-heading']);
        } else {
            $html = \html_writer::start_tag('section', ['class' => 'ec-nav-group']);
            $html .= \html_writer::tag('h2', get_string($titlekey, 'theme_echealth'), ['class' => 'ec-nav-heading']);
        }
        $html .= \html_writer::start_tag('ul', ['class' => 'ec-nav-list']);
        foreach ($items as $item) {
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
            $html .= \html_writer::tag('li', \html_writer::tag('a', $content, $attributes));
        }
        $html .= \html_writer::end_tag('ul') . \html_writer::end_tag($collapsible ? 'details' : 'section');
        return $html;
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
            'id, fullname, shortname, summary, category, startdate, enddate, enablecompletion, cacherev',
            'sortorder ASC'
        );
        $coursecount = count($courses);
        $completedcount = $DB->count_records_select(
            'course_completions',
            'userid = :userid AND timecompleted > 0',
            ['userid' => $USER->id]
        );
        $coursecards = $this->echealth_course_cards(array_slice($courses, 0, 6));
        $upcoming = $this->echealth_upcoming_events($courses);
        $announcements = $this->echealth_announcements();

        $html = \html_writer::start_tag('section', [
            'class' => 'ec-dashboard-panel',
            'aria-labelledby' => 'ec-dashboard-welcome',
        ]);
        $html .= \html_writer::tag('p', get_string('dashboardeyebrow', 'theme_echealth'), ['class' => 'ec-dashboard-eyebrow']);
        $html .= \html_writer::tag('h2', get_string('welcomeuser', 'theme_echealth', fullname($USER)), ['id' => 'ec-dashboard-welcome']);
        $introkey = 'dashboardintro';
        if (has_capability('moodle/site:config', \context_system::instance())) {
            $introkey = 'dashboardintroadmin';
        } else {
            foreach (array_slice($courses, 0, 6) as $course) {
                if (has_capability('moodle/course:update', \context_course::instance($course->id))) {
                    $introkey = 'dashboardintroteacher';
                    break;
                }
            }
        }
        $html .= \html_writer::tag('p', get_string($introkey, 'theme_echealth'), ['class' => 'ec-dashboard-intro']);
        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-stats', 'aria-label' => get_string('learningoverview', 'theme_echealth')]);
        $html .= $this->echealth_stat_card('book-open', 'enrolledcourses', (string)$coursecount);
        $html .= $this->echealth_stat_card('check-circle', 'completedcourses', (string)$completedcount);
        $html .= \html_writer::end_tag('div');

        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-columns']);
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-courses-heading']);
        $html .= \html_writer::tag('h3', get_string('yourcourses', 'theme_echealth'), ['id' => 'ec-courses-heading']);
        if ($coursecards) {
            $html .= \html_writer::tag('div', implode('', $coursecards), ['class' => 'ec-course-grid']);
        } else {
            $html .= $this->echealth_empty_state('book-open', 'nocourses', 'browsecatalogue', new \moodle_url('/course/index.php'));
        }
        $html .= \html_writer::end_tag('section');

        $html .= \html_writer::start_tag('div', ['class' => 'ec-dashboard-aside']);
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-upcoming-heading']);
        $html .= \html_writer::tag('h3', get_string('upcomingactivities', 'theme_echealth'), ['id' => 'ec-upcoming-heading']);
        $html .= $upcoming ?: $this->echealth_empty_state('calendar', 'noupcomingactivities', 'viewcalendar', new \moodle_url('/calendar/view.php'));
        $html .= \html_writer::end_tag('section');
        $html .= \html_writer::start_tag('section', ['class' => 'ec-dashboard-section', 'aria-labelledby' => 'ec-announcements-heading']);
        $html .= \html_writer::tag('h3', get_string('announcements', 'theme_echealth'), ['id' => 'ec-announcements-heading']);
        $announcementurl = $this->site_announcements_url();
        $html .= $announcements ?: $this->echealth_empty_state('announcement', 'noannouncements',
            $announcementurl ? 'viewannouncements' : null, $announcementurl);
        $html .= \html_writer::end_tag('section');
        $html .= \html_writer::end_tag('div');
        $html .= \html_writer::end_tag('div');
        $html .= \html_writer::end_tag('section');
        return $html;
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
    private function echealth_course_cards(array $courses): array {
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
            if (!empty($course->enablecompletion)) {
                $progress = \core_completion\progress::get_course_progress_percentage($course);
            }
            $meta = \html_writer::tag('span', $categoryname, ['class' => 'ec-course-category']);
            if ($progress !== null) {
                $progressvalue = (int)floor($progress);
                $meta .= \html_writer::tag('div',
                    \html_writer::tag('span', '', ['class' => 'ec-progress-value', 'style' => 'width:' . $progressvalue . '%']),
                    ['class' => 'ec-progress', 'role' => 'progressbar', 'aria-valuenow' => $progressvalue,
                        'aria-valuemin' => 0, 'aria-valuemax' => 100, 'aria-valuetext' => get_string('progress', 'theme_echealth', $progressvalue),
                        'aria-label' => get_string('courseprogress', 'theme_echealth', format_string($course->fullname))]
                );
            } else {
                $meta .= \html_writer::tag('span', get_string('progressnottracked', 'theme_echealth'), ['class' => 'ec-progress-note']);
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
            $footer = \html_writer::link($courseurl, get_string('opencourse', 'theme_echealth'), ['class' => 'ec-course-action']);
            $cards[] = \html_writer::tag('article', $image .
                \html_writer::div($meta . $link . $footer, 'ec-course-card-content'), ['class' => 'ec-course-card']);
        }
        return $cards;
    }

    /**
     * Match only the six sample course names included in the supplied kit.
     *
     * @param string $fullname
     * @return string|null
     */
    private function echealth_sample_course_image(string $fullname): ?string {
        $name = core_text::strtolower($fullname);
        $samples = [
            'infection prevention' => 'infection-prevention.webp',
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

        if (!$courses) {
            return '';
        }
        require_once($CFG->dirroot . '/calendar/lib.php');
        $courseids = array_map(static fn($course) => (int)$course->id, $courses);
        $events = calendar_get_events(time(), time() + (30 * DAYSECS), (int)$USER->id, false, $courseids, true, true);
        if (!$events) {
            return '';
        }
        $items = [];
        foreach (array_slice($events, 0, 4) as $event) {
            $date = userdate($event->timestart, get_string('strftimedate', 'langconfig'));
            $url = new \moodle_url('/calendar/view.php', ['view' => 'day', 'time' => $event->timestart]);
            $items[] = \html_writer::tag('li',
                \html_writer::tag('span', $date, ['class' => 'ec-event-date']) .
                \html_writer::link($url, format_string($event->name), ['class' => 'ec-event-title']));
        }
        return \html_writer::tag('ul', implode('', $items), ['class' => 'ec-event-list']);
    }

    /**
     * Render recent items from the real site news forum, when one is configured and accessible.
     *
     * @return string
     */
    private function echealth_announcements(): string {
        global $CFG;

        $module = $this->get_site_announcement_module();
        if (!$module) {
            return '';
        }
        [$cmid, $context] = $module;
        if (!has_capability('mod/forum:viewdiscussion', $context)) {
            return '';
        }
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        $cm = get_coursemodule_from_id('forum', $cmid, SITEID, false, MUST_EXIST);
        $discussions = forum_get_discussions($cm, 'p.modified DESC', true, -1, 3);
        if (!$discussions) {
            return '';
        }
        $items = [];
        foreach ($discussions as $discussion) {
            $url = new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->discussion]);
            $items[] = \html_writer::tag('li', \html_writer::link($url, format_string($discussion->name)));
        }
        return \html_writer::tag('ul', implode('', $items), ['class' => 'ec-announcement-list']);
    }

    /**
     * Return the site announcements destination or the front page when no news forum exists.
     *
     * @return \moodle_url
     */
    private function site_announcements_url(): ?\moodle_url {
        $module = $this->get_site_announcement_module();
        return $module ? new \moodle_url('/mod/forum/view.php', ['id' => $module[0]]) : null;
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
        $iconhtml = \html_writer::empty_tag('img', [
            'src' => $this->image_url('icons/' . $icon, 'theme_echealth')->out(false),
            'alt' => '',
            'aria-hidden' => 'true',
            'class' => 'ec-empty-icon',
        ]);
        $html = $iconhtml . \html_writer::tag('p', get_string($messagekey, 'theme_echealth'));
        if ($actionkey && $url) {
            $html .= \html_writer::link($url, get_string($actionkey, 'theme_echealth'), ['class' => 'ec-inline-action']);
        }
        return \html_writer::div($html, 'ec-empty-state');
    }
}
