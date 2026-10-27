# Department Moodle 5.3+ implementation TODO

**Updated:** 27 October 2026

## P0 — Close safety and functional verification gaps

- [ ] Complete local source review of installed Attendance and Custom Certificate plugins and review their current Marketplace advisories.
- [ ] Agree on Attendance session/status rules and create a test activity in a designated demo course; test lecturer marking, learner visibility, report and export permissions.
- [ ] Approve certificate wording, department logo use, template, issuing authority, completion/eligibility criteria and verification policy; then configure a test certificate and test PDF download with a learner account.
- [ ] Run account-based acceptance with Admin User, an editing teacher and a student. Verify positive and negative capabilities for users, enrolments, grades, reports, question bank, attendance and certificates.
- [ ] Complete mobile-width navigation, keyboard, focus, screen-reader labels, responsive content and link-destination review.
- [ ] Review the local PHP runtime against the supported Moodle 5.3 matrix before any production planning.

## P1 — Build native departmental workflows

- [ ] Define authorized scope for campus administrators and department managers; Moodle categories/cohorts alone do not isolate campus records.
- [ ] Implement a capability-safe lecturer/learner course-selection overview, using native participants/enrolment screens and avoiding a new student-information system.
- [ ] Evaluate whether native Report Builder satisfies approved course, completion, assessment and attendance reporting needs; define least-privilege report audiences and verify CSV export.
- [ ] Configure approved Activity Completion and Course Completion criteria on representative courses; verify dashboard progress with test learner data.
- [ ] Configure Feedback for an approved course evaluation and verify response privacy.
- [ ] Add a course-scoped assessment index or implement a small Moodle local plugin if cross-course assignment/quiz aggregation is still required. Use Moodle APIs, context checks, pagination and capability checks.
- [ ] Set up timetable/calendar events using Moodle Calendar; specify any external timetable source before integration work.

## P2 — Optional plugin evaluation

- [ ] Retry download/install of `mod_checklist` 4.1.0.9 only from the official Moodle Marketplace or upstream repository after the browser download authority is available; keep it optional if core completion criteria meet the need.
- [ ] Retry download/install of `format_flexsections` 5.0.5 from the official Marketplace/upstream tag only after the download authority is available. Test on a course copy; do not convert existing courses automatically.
- [ ] Consider `block_teacher_checklist` v1.3.3 (explicit 5.3 release) only if teachers need its block workflow; install and validate when package download access is available.
- [ ] Do not install `format_softcourse`, Completion Progress, Configurable Reports, Questionnaire, Custom SQL Reports, or Tiles until an official maintained 5.3-compatible release is verified.
- [ ] Re-audit Marketplace metadata when these plugins publish new releases; the compatibility matrix is time-specific.

## P3 — Delivery evidence and operations

- [ ] Save desktop and mobile screenshots in the project docs/artifacts after role-based QA.
- [ ] Record each acceptance case, account role/capabilities, expected result, actual result and evidence.
- [ ] Review backups and practice restoring the database plus Moodle data and source into an isolated copy.
- [ ] Document cron, HTTPS, mail delivery, privacy retention, admin access and plugin update procedures before production use.

## Backup and change-control state

- [x] Create a development-only pre-plugin backup at `backups/dev-pre-plugin-20261027/` containing the application, Moodle data and SQL dump.
- [ ] Do not install or upgrade against production automatically.
- [ ] A browser download authority check denied access to the official compatible candidate package during this run; no workaround was attempted.
