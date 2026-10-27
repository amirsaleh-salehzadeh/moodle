# Department LMS requirements coverage

**Audit date:** 27 October 2026  
**Environment:** local Moodle 5.3+ (branch 503), theme `echealth` inheriting Boost.  
**Data:** development demo data; no production information was assessed.

## Status definitions

- **Implemented and tested** — available in local code and observed in a local account/workflow.
- **Configured but unverified** — implementation/configuration exists, but required role or end-to-end workflow has not been tested.
- **Missing** — no current route, configuration or feature is present to meet the requirement.
- **Blocked** — work cannot safely proceed because a required approval, compatible package, backup/download gate or test account is unavailable.

| Requirement / interface | Status | Evidence and limitation |
|---|---|---|
| Moodle version, installed plugins and local records audited | **Implemented and tested** | Core reports 5.3+ branch 503 build 20261021; installed component records queried; counts summarized below. |
| Department logo, palette and Boost child theme | **Implemented and tested** | Local homepage render shows the supplied Eastern Cape logo, green/gold/red/blue stripe and `theme_echealth` sidebar. |
| Global learning sidebar and dashboard | **Implemented and tested** | Dashboard, site home, My Courses, catalogue, calendar, notifications, badges and support appeared in the local admin desktop render. Role-specific dashboard data has not been tested. |
| Navigation group structure from `NAVIGATION_STRUCTURE.md` | **Implemented and tested** | Sidebar renderer uses separate Learner Management, Management & Reports and System Administration disclosures; local desktop render confirmed these headings. |
| Learner management | **Configured but unverified** | Capability-gated Moodle user, course and cohort routes exist. No learner-account workflow was performed. There is no separate SIS or campus tenant isolation. |
| Lecturer management | **Missing** | No dedicated lecturer directory/assignment overview. Native course participants/enrolment routes are available only inside authorized course contexts. |
| Courses and subjects | **Configured but unverified** | 36 visible courses across 7 categories; catalogue and course-management routes exist. No course edit/create workflow was tested. |
| Exams and assessments | **Configured but unverified** | Core Quiz, question bank and Assignments are installed; 36 assignment instances and zero quiz instances. No cross-course assessment overview or attempt/grade data exists. |
| Assignments and submissions | **Configured but unverified** | Course-scoped Assignment links exist; no submission or grading workflow was run. |
| Attendance | **Configured but unverified** | `mod_attendance` code/version is installed and branch 503 compatible; zero attendance activities/sessions. Plugin download is not needed, but approved session setup and role workflow remain outstanding. |
| Timetables and calendar | **Configured but unverified** | Moodle Calendar route and theme event panel exist. No timetable plugin/integration or representative calendar event has been verified. |
| Results and grades | **Configured but unverified** | Gradebook and grade links are present. Zero grade rows; visibility and lecturer export paths untested. |
| Reports and analytics | **Configured but unverified** | Moodle core Report Builder route/classes exist. No role-scoped report definitions or exports have been configured/verified. |
| Certificates | **Configured but unverified** | Custom Certificate and elements are installed; zero certificate activities/issues. Department template and eligibility conditions are not approved/configured. |
| Communication and notifications | **Configured but unverified** | Moodle messaging, notification and calendar routes exist; no notification delivery/read-state or announcement workflow was tested. |
| Administration | **Configured but unverified** | System administration disclosure and capability-gated routes render for admin. No settings were changed during audit. |
| Mobile responsiveness and keyboard/screen-reader behavior | **Configured but unverified** | Existing CSS/AMD supports responsive navigation and accessible disclosure markup; no mobile account/browser QA was completed in this pass. |
| Role tests for admin, lecturer and learner | **Blocked** | Local session was authenticated as Admin User. Teacher/student permission gates are capability-based in source, but separate authenticated role workflows were not exercised. |
| Compatible optional checklist/course-format packages | **Blocked** | `mod_checklist` 4.1.0.9 and `format_flexsections` 5.0.5 have explicit 5.3 release metadata, but the browser download authority gate blocked package acquisition. They remain uninstalled. |
| Unsupported plugin alternatives | **Implemented and tested** | Core Feedback, Report Builder and completion APIs exist; optional Questionnaire, Configurable Reports, Custom SQL, old Tiles, Teacher Checklist and Soft Course Format are not installed. |

## Current local data inventory

- 41 undeleted users including guest/admin/demo accounts.
- 36 visible courses; 7 categories.
- 36 editing-teacher course role assignments; 180 student course role assignments.
- 216 user-enrolment rows.
- 36 Assignments; zero quizzes, Attendance instances, Custom Certificate instances, Feedback instances, grade rows, or course-completion rows.
- Moodle core `mod_feedback`, Report Builder and the department theme are present.

These counts describe the local database at audit time; they do not establish that a requested departmental process is operational.
