# Eastern Cape Health Moodle navigation

**Current implementation map — 27 October 2026**

The authenticated global sidebar is rendered by `theme_echealth` in `public/theme/echealth/classes/output/core_renderer.php` and inherits Boost. This document is the navigation structure source of truth for the renderer. It uses Moodle URLs, context checks, and `has_capability()` gates; it does not infer authorization from role-name strings. Course activities remain under real course contexts. On anonymous pages, Boost-native public navigation remains in use.

The rendered top-level groups follow this document: **Learning**, **Teaching & Assessments**, **Learner Management**, **Management & Reports**, **System Administration**, and **Help & Support**. The three management groups are separate disclosures, not combined under a generic Administration heading. Empty groups are omitted for users who have no authorized destinations in them.

## Global sidebar

| Group | Entry | Destination / visibility |
|---|---|---|
| Learning | Dashboard | `/my/`, if dashboard is enabled |
| Learning | Site home | `/` |
| Learning | My Courses | `/my/courses.php` |
| Learning | Search Moodle | `/search/index.php`, when global search is enabled |
| Learning | Course Catalogue | `/course/index.php` |
| Learning | Calendar | `/calendar/view.php` |
| Learning | Notifications | Native popup notifications route when present |
| Learning | My badges | Own badge page when badges are enabled and the user has the own-badge capability |
| Learning | Announcements | Configured site news forum; course news forums are linked inside the relevant course submenu |
| Teaching & Assessments | Course activities | Per-course submenu for accessible enrolled courses; only links for installed routes and permitted capabilities appear |
| Learner Management | Users | `/admin/user.php` for users with user create/update capability |
| Learner Management | Courses | `/course/management.php` for course managers with appropriate course-management capability |
| Learner Management | Cohorts & Campuses | `/cohort/index.php` for users with cohort management capability |
| Learner Management | Competencies | Site competency management for users with the management capability |
| Management & Reports | Reports | Report Builder for site config-capable users, or an accessible course log report where available |
| System Administration | Site Administration, Roles, Authentication, Plugins, Scheduled Tasks, badge management | Added only when routes exist and relevant site capabilities are granted |
| Help & Support | Support | Configured support page, otherwise validated support email; hidden if neither is configured |

Learning and course activity links are rendered from the `Learning` and `Teaching & Assessments` groups. User, course, cohort and competency administration routes go in `Learner Management`; report routes go in `Management & Reports`; privileged site configuration links go in `System Administration`.

## Course-context submenu

The theme checks each accessible enrolled course and its visible modules. Depending on capabilities and configuration it can show the course, Assignments (`/mod/assign/index.php?id=COURSEID`), Exams & Quizzes (`/mod/quiz/index.php?id=COURSEID`), Gradebook, Completion report, Question Bank, Enrolment methods, news forum and accessible Attendance activity. Attendance and Custom certificate are optional and their links appear only if a visible activity exists and the user can access it. This avoids inventing global Moodle activity routes.

## Role and authorization model

- Learners see their own dashboard, courses, visible activities, grades permitted by Moodle, calendar, notifications and eligible badges/certificates.
- Teaching staff see course activities only in courses they can access and only where the capability allows the relevant action/report.
- Course/category managers gain only the management/report links allowed by their assignments and capabilities.
- Site administration and global user management require system-context capabilities.
- Users with multiple role assignments receive the union of actions allowed in each applicable Moodle context.

## Current gaps against the requested target

- There is not yet a cross-course assignment/exam aggregation page with filters, due dates, attempt/submission states and grading status. Current assignment and quiz entry points are per course.
- Management reporting is an entry point to existing Report Builder or course logs, not a bespoke campus overview. Report Builder route is restricted to site configuration capability in the current sidebar.
- No dedicated campus-isolated learner directory or enrolment workflow is implemented. Moodle user, cohort and course-management screens are linked where capabilities allow; categories/cohorts do not create tenant isolation.
- There is no standalone Help Centre/FAQ content if the site has not configured a support page or support email.
- Attendance and Custom certificate are installed, but no course activities have been configured. Their features are therefore not visible as global activity destinations.
- Mobile, role-specific and populated workflow acceptance checks remain outstanding as recorded in `NAVIGATION_UI_QA.md`.
