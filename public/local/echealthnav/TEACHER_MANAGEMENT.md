# Teacher management

The **Teachers & Clinical Educators** entry in People Management opens a server-rendered staff directory at `index.php?view=teachers`. The directory reads Moodle user accounts and role assignments; it does not maintain a separate staff database. Users assigned a standard editing teacher, teacher or non-editing teacher role, or a role whose name contains “Clinical Educator”, in course or course-category context are included. Multiple assignments are deduplicated.

Search covers name, username and email. Account status, page size and pagination are applied to the directory results. Course assignments are linked to course-context assignments and assignments at the immediate category context. A teacher profile link opens Moodle's own profile; Add Teacher and Edit open Moodle's supported account form. Moodle's native capabilities continue to authorize those operations.

The directory intentionally labels username as “Username / Staff ID”: no custom staff-ID profile field is assumed to exist. Job title, campus, department, qualifications and professional registration are not inferred. Workload and performance indicators are not presented yet; reliable calculations need defined date windows, assignment grading-state rules, category hierarchy semantics and privacy/reporting policy. No performance score or contractual teaching-hour estimate is generated.

Access to the directory requires `moodle/user:viewdetails` and account creation or update capability (or site configuration capability). The page uses Moodle's configured roles and core account pages. Add site-specific Clinical Educator roles through Moodle role administration; this interface does not create or assign a new global role.
