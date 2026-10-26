# Eastern Cape Health Moodle feature matrix

“Configured” means this checkout contains a real Moodle configuration or record. It does not mean a full end-to-end test has been completed.

| Requirement | Status | Evidence / limitation |
|---|---|---|
| Upgrade-safe department theme | Configured | Boost child theme at `public/theme/echealth`; selected as the active site theme. |
| Supplied logo and government-colour header | Configured | Original provided PNG is served from Moodle's theme image API; masthead, multicolour strip, palette, responsive rules and keyboard focus styles are implemented. Responsive/contrast QA remains outstanding. |
| Public homepage and branded login | Configured | Department title, portal introduction and demonstration notice are set on the Moodle site front page; campus catalogue/search is enabled. Login uses a theme template. |
| Student/lecturer/administrator dashboards | Partial | Native Moodle dashboards and course-role capabilities are available. Synthetic learner, editing-teacher and manager accounts exist; custom dashboard layouts and role workflow checks are not done. |
| Campuses, cohorts, courses and enrolment | Configured | Six fictional campus categories/cohorts, 36 campus-specific course offerings, 30 learner accounts, six educator accounts and 180 course enrolments. |
| Course learning content | Configured | Each course has four topic sections and a core Moodle learning guide page. Materials are fictional orientation content, not clinical protocols. |
| Quiz assessments | Partial | Moodle core Quiz is installed, but no quiz activity, attempt or mark has been created. |
| Assignment assessments | Configured | Each course has a core Moodle reflective Assignment with online text/file submission and educator comments enabled. No submissions or marks are seeded. |
| Gradebook, completion and badges | Partial | Course completion tracking is enabled. No sample grades or completions were seeded; badge issuing is not configured. |
| Two training administrators and a training manager | Configured | Three fictional accounts use Moodle's built-in manager role at system context; credentials are stored in the local data directory. |
| Calendar, notifications and announcements | Partial | Moodle core provides these features; no sample events, announcements or reminder workflows were configured. |
| Certificates and attendance | Plugin | No third-party plugins installed. Plugin versions and compatibility must be reviewed for this Moodle build. |
| Reports and training management views | Partial | Moodle native reports are available; no demo report views, exports or aggregate dashboard were configured. |
| HEMIS, HEQCIS, NLRD, DHET or CHE submissions | Not supported | Future custom reporting/integration work. No claim of complete regulatory reporting. |
| Admissions, finance, hospital placements, Persal, student lifecycle or full campus tenancy | Not supported | These enterprise LMIS functions are outside Moodle's configured LMS scope. Categories and cohorts do not isolate campuses as tenants. |
| POPIA or government security certification | Not supported | A Moodle configuration does not itself establish legal, clinical or government security compliance. |
| End-to-end workflow, accessibility and screenshots | Outstanding | Moodle record counts and the live theme endpoints can be checked, but learner/lecturer workflows, assistive technology, device layouts and screenshots still need review. |

No third-party plugins were installed. The local XAMPP PHP 8.5 runtime is for demonstration; Moodle labels support for PHP 8.5 as in development. See the [implementation report](echealth-implementation-plan.md) for runtime details and remaining work.
