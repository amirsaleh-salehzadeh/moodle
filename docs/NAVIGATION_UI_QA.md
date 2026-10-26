# Eastern Cape Health Moodle navigation and UI QA

**Review date:** 27 October 2026
**Scope:** `ec-health-moodle-ui-kit/prompts/01_IMPLEMENT_NAVIGATION.md` and `02_QA_AND_ACCESSIBILITY.md`  
**Runtime:** local Moodle 5.3+ (`http://localhost:8080`), authenticated as Admin User  
**Result:** **Implementation complete for the theme-level rollout; full acceptance QA remains partial.**

## Implemented

- Added an authenticated, capability-aware global sidebar with Dashboard, Site home, My Courses, Course Catalogue, Calendar, conditional Certificates, contextual Reports, and one collapsible Administration group. Each management destination is included only when its route and capability are available. Course menus expose Assignments, Quizzes, Grades, Question Bank, Enrolments, and optional Attendance when the current user has course-level access. Help & Support uses configured site support details. Anonymous navigation continues to use Boost's native menu.
- Reworked the authenticated dashboard using Moodle renderer data: real enrolments/completions, course links/categories/course images, progress, upcoming site/course/group/user events, recent accessible site/course news discussions, and an unread Moodle notification count with inbox/preferences links. Empty data renders honest empty states. Existing Moodle dashboard blocks/editing remain available below the theme content.
- The dashboard now renders capability-based student, lecturer, and site-administrator views. Students see their enrolled-course progress/completions and discovery when unenrolled. Lecturers see teaching courses without personal progress indicators, an Assignment API pending-grading count, and a Review grading action when permitted. Site administrators see live active-account and visible-course totals plus capability-filtered management actions. Coordinator copy remains available. These role views are implemented but still need separate-account browser acceptance.
- Added own-grade summaries guarded by course grade visibility and Moodle grade-report capabilities, completion totals scoped to enrolled completion-enabled courses, and earned-badge display plus learner/admin badge links guarded by Moodle settings and capabilities. Current records contain no grades, completions or badges, so no example awards or marks were added.
- When a user has no personal enrolments, the course panel now shows up to six distinct, visible Moodle catalogue offerings as discovery cards. They link to real courses and are labelled as catalogue offerings, without implying enrolment or showing fake progress.
- Added responsive 3/2/1 course-card layout and desktop navigation rail, uploadable department logo and configurable department/portal labels, supplied SVG icons, government stripe, placeholder artwork, and six local WebP sample images.
- Updated the masthead stripe proportions and portal descriptor to match the kit tokens/reference; added its hero panel graphic, course metadata/action icons, event and announcement icons, and capability-filtered dashboard quick actions. Theme colors, borders, radii, and shadow now follow `styles/tokens.css`.
- Added accessible navigation drawer behavior: Escape closes, Tab is contained, outside focus is redirected, and `aria-expanded` follows Boost drawer events. Boost handles its own focus restoration and backdrop behavior.
- Preserved the existing logo byte-for-byte, Boost inheritance, Moodle core, and native catalogue/course pages. Added Moodle language strings and theme pluginfile support for the configured logo.

## Evidence and checks

- Inspected the live authenticated dashboard and a course/home page in Brave at approximately 1920×1080. The sidebar, header branding, active Dashboard route, live empty-state cards, native Moodle edit mode, notifications, messaging, and user menu rendered. Admin User showed the available Users, Courses, Cohorts & Campuses, Reports, and Site Administration actions.
- Dashboard data in this local session is genuinely empty: zero personal enrolments/completions, no upcoming course events or site announcements. The visible counts and empty states match the live account data; no sample records were inserted. Calendar now also includes personal and site events even for accounts without course enrolments.
- Moodle cache purge completed successfully with the local PHP 8.5 CLI. Theme SCSS compiled and appeared in the live page after cache purge.
- PHP lint passed for `classes/output/core_renderer.php`, `settings.php`, and `lib.php`.
- Fixed a runtime exception in the no-enrolment course fallback by fully qualifying Moodle's global `\core_text` class; PHP lint passes and Moodle caches were purged afterward.
- JavaScript syntax parsing passed for the AMD source in module mode and for `amd/build/navigation.min.js`.
- Supplied logo SHA-1 matches the existing theme logo. The supplied 44 SVGs, six WebPs, government stripe, and placeholder are copied into theme assets.
- Asset use is context-sensitive: the sidebar and dashboard use relevant kit SVGs. Certificates and Attendance appear only when their plugins/activities exist and are visible to the user. All six WebPs are configured as course-name fallbacks when the real course title matches, after Moodle's own course image. The current seeded catalogue has different course titles, so several kit sample images won't appear for those records. The JPEG alternatives, preview screenshot, and unused decorative graphics are not runtime UI assets.
- The reference's learner/staff totals, deadlines, campuses, and course progress are illustrative. The implementation uses live Moodle values and does not adopt those sample records or claims.
- The installed Moodle tree contains core Assignments, Quiz, Gradebook, Question Bank, cohorts, and competencies. Moodle 5.3-compatible Attendance (`mod_attendance`) and Custom certificate (`mod_customcert`) plus certificate elements are now installed and their CLI upgrade completed. No Attendance or Custom certificate activities are configured in the demo courses, so no such links appear yet; the conditional logic exposes them when an accessible activity exists.
- Git status shows changes only in the child theme and project documentation/UI kit; Moodle core was not edited.

## Checklist

- [x] Inspect installed Moodle version, active child theme, routes and authenticated dashboard.
- [x] Preserve existing logo, palette, Boost inheritance, and native catalogue.
- [x] Add live capability-aware sidebar and dashboard content.
- [x] Provide authenticated Site home and course-scoped Enrolments/Grades navigation; add conditional Certificates/Attendance activity navigation.
- [x] Add role-specific dashboard introductions and a real pending-grading count for users with grading permissions.
- [x] Integrate supplied icons and local course art with fallback behavior.
- [x] Align masthead, hero panel, tagline, tokens, card image proportions, and dashboard actions with the UI kit reference.
- [x] Add responsive course cards and keyboard-accessible drawer handling.
- [x] Purge caches and visually verify the authenticated desktop rendering.
- [x] Lint PHP and parse AMD source/build JavaScript.
- [ ] Verify signed-in student, lecturer, coordinator and site-admin roles separately.
- [ ] Capture screenshots and inspect at 1440, 1024, 768 and 390px.
- [ ] Complete screen-reader/contrast/keyboard testing and console/link audit.
- [ ] Exercise data-rich course, progress, calendar, notification inbox/read-state and announcement states; verify configured email delivery or keep the disabled-mail limitation explicit.

## Remaining QA limits

The only live authenticated account available during this check was Admin User. No role switching or seeded records were performed, so role-specific visibility and populated personal dashboards still need acceptance verification. The screenshot/reference was not captured into the repository, and the browser viewport remained desktop size. Screen-reader, contrast, mobile drawer interaction and browser-console checks were not independently completed. The drawer code follows the Boost event API, but focus restoration/backdrop should be confirmed interactively at mobile width before declaring all prompt acceptance criteria passed.

The latest hero, tagline, quick-action, token, no-enrolment catalogue fallback, and navigation feature additions were source-checked. A fresh browser screenshot after the latest feature additions has not been captured; role- and plugin-conditional routes need account-based verification.

Attendance and Custom certificate plugins are installed, but those destinations correctly remain hidden until course activities exist and are accessible. Enrolments and Grades are course-context links and appear only for users with the matching course capability. The dashboard now distinguishes learner, teacher, coordinator, and site-admin introductions and includes grader counts where permission is present, but separate role accounts and populated workflows still need acceptance testing. Do not claim the broader Moodle demo is production- or role-acceptance-ready based on this UI rollout.

## Files changed

- `public/theme/echealth/classes/output/core_renderer.php`
- `public/theme/echealth/settings.php`, `public/theme/echealth/lib.php`
- `public/theme/echealth/templates/theme_boost/navbar.mustache`
- `public/theme/echealth/templates/theme_echealth/mobile_navigation.mustache`
- `public/theme/echealth/amd/src/navigation.js`, `amd/build/navigation.min.js`
- `public/theme/echealth/scss/echealth.scss`
- `public/theme/echealth/lang/en/theme_echealth.php`
- `public/theme/echealth/pix/icons/`, `pix/course/`, and `pix/graphics/`
- `TODO.md`
- `docs/NAVIGATION_UI_QA.md`
