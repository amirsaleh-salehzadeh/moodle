# Eastern Cape Health Moodle implementation

## Environment

- Moodle 5.3+ (branch 503) with the public web root at `public/`.
- Local MariaDB database `moodle_local_20261026`; Moodle URL `http://localhost:8080`.
- XAMPP PHP 8.5.12 is used for Moodle CLI and the local PHP web server. Moodle 5.3 requires PHP 8.3 or newer and `sodium`; PHP 8.5 support is still marked in development by Moodle. This is a local demonstration runtime, not a production hosting recommendation. [Moodle 5.3 release requirements](https://moodledev.io/general/releases/5.3)
- Demo records were added to the isolated local database used by this checkout. The existing guest and administrator records were retained. Synthetic demo accounts use reserved `example.test` addresses; no actual patient information is present.

## Implemented

1. Added and activated the upgrade-safe `theme_echealth` child theme under `public/theme/echealth`. It inherits Boost and includes a responsive department masthead, official supplied PNG, government colour stripe, Moodle language strings, restrained palette and focus styling.
2. Applied the department name, portal title and demonstration notice to the Moodle front page. The public front page displays the campus category catalogue and course search; logged-in learners see categories and their enrolled courses.
3. Seeded six illustrative campus categories and cohorts, each with six course offerings (36 courses total), course sections, completion tracking, manual enrolments, one core Moodle learning guide Page and one Moodle Assignment per course.
4. Seeded 30 fictional learners, six fictional educators, two training administrator accounts and one training manager account. Campus learners are cohort members and are enrolled in that campus's six courses; educators are enrolled as editing teachers.
5. Added scripts to seed the course and roster records and to add course learning pages. Generated passwords are saved outside the web root under Moodle's local data directory.

## Not implemented or not validated

- Assignment submission and grading workflows are available in the seeded courses, but no sample submissions or marks exist. Quizzes, sample attempts, sample completions, certificates, attendance, calendar entries, announcements, customized role dashboards and management reports have not been seeded or validated.
- No third-party plugins were installed. Attendance and certificates need a version-specific plugin selection and compatibility review.
- Keyboard, contrast, mobile/tablet visual QA, learner and educator end-to-end workflow checks and screenshot capture remain outstanding.
- Moodle's PHP 8.5 support status and the local development server are unsuitable as a production deployment. Configure production hosting with a supported, secured PHP runtime, HTTPS, operational backups and institutional access controls.
- Moodle alone does not establish POPIA, government security or clinical governance compliance. Admissions, student finance, hospital placement allocation, Persal integration, national regulatory submissions and full campus tenancy remain future LMIS work.

See [installation and local run instructions](echealth-installation.md) and the [feature compliance matrix](echealth-compliance-matrix.md).
