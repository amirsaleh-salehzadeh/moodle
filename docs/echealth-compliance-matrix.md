# Eastern Cape Health Moodle feature matrix

“Configured” means this checkout contains a real Moodle configuration or record. It does not mean a full end-to-end test has been completed.

| Requirement | Status | Evidence / limitation |
|---|---|---|
| Upgrade-safe department theme | Configured | Boost child theme at `public/theme/echealth`; selected as the active site theme. |
| Supplied logo and government-colour header | Configured | Original provided PNG is served from Moodle's theme image API; masthead, multicolour strip, palette, responsive rules and keyboard focus styles are implemented. Responsive/contrast QA remains outstanding. |
| Public homepage and branded login | Configured | Department title, portal introduction and demonstration notice are set on the Moodle site front page; campus catalogue/search is enabled. Login uses a theme template. |
| Student/lecturer/administrator dashboards | Partial | Theme renders capability-based student, lecturer and administrator summaries using Moodle data; separate account workflow acceptance remains outstanding. |
| Campuses, cohorts, courses and enrolment | Configured | Six fictional campus categories/cohorts, 36 campus-specific course offerings, 30 learner accounts, six educator accounts and 180 course enrolments. |
| Course learning content | Configured | Each course has four topic sections and a core Moodle learning guide page. Materials are fictional orientation content, not clinical protocols. |
| Quiz assessments | Partial | Moodle core Quiz is installed, but no quiz activity, attempt or mark has been created. |
| Assignment assessments | Configured | Each course has a core Moodle reflective Assignment with online text/file submission and educator comments enabled. No submissions or marks are seeded. |
| Gradebook, completion and badges | Partial | Dashboard exposes the signed-in user's course final grades only where Moodle permits grade viewing and course grades are visible; completion counts are scoped to enrolled courses with completion enabled, and course cards use Moodle's completion progress API. Earned badges link to Moodle's badge pages, with role/capability-filtered learner and badge-management navigation. Badges are enabled on this site, but there are no grade values, completed courses, or badge definitions/awards in the current data; activity criteria and award workflows still require configuration and acceptance. |
| Two training administrators and a training manager | Configured | Three fictional accounts use Moodle's built-in manager role at system context; credentials are stored in the local data directory. |
| Calendar, notifications and announcements | Partial | Dashboard shows visible upcoming site, course, group and user events; recent site/course news forum discussions; unread Moodle notifications and native links to notification inbox/preferences. Header retains Moodle's notification popover. The current demo has no seeded events or announcements, and mail delivery/preferences have not been acceptance-tested. |
| Certificates and attendance | Plugin configured | Moodle 5.3-compatible Attendance (`mod_attendance`) and Custom certificate (`mod_customcert`) are installed with Custom certificate elements. Course activities have not been configured or exercised. |
| Reports and training management views | Partial | Moodle native reports are available; no demo report views, exports or aggregate dashboard were configured. |
| HEMIS, HEQCIS, NLRD, DHET or CHE submissions | Not supported | Future custom reporting/integration work. No claim of complete regulatory reporting. |
| Admissions, finance, hospital placements, Persal, student lifecycle or full campus tenancy | Not supported | These enterprise LMIS functions are outside Moodle's configured LMS scope. Categories and cohorts do not isolate campuses as tenants. |
| POPIA or government security certification | Not supported | A Moodle configuration does not itself establish legal, clinical or government security compliance. |
| End-to-end workflow, accessibility and screenshots | Outstanding | Moodle record counts and the live theme endpoints can be checked, but learner/lecturer workflows, assistive technology, device layouts and screenshots still need review. |

The local XAMPP PHP 8.5 runtime is for demonstration; Moodle labels support for PHP 8.5 as in development. See the [implementation report](echealth-implementation-plan.md) for runtime details and remaining work.
