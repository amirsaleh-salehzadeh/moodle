# Eastern Cape Department of Health Moodle Demo — Implementation TODO

## Project objective

Turn the existing Moodle 5.3+ local proof of concept into a credible, safe, professional nursing education demonstration. This is an evidence-led implementation plan. No feature changes were made while preparing it.

## Current implementation summary (audited 26 October 2026)

- **Verified present:** Moodle 5.3+ (branch 503), local site returns HTTP 200 at `http://localhost:8080`; `theme_echealth` is configured as active and inherits Boost. The logo asset, masthead, four-colour stripe, palette, responsive rules, focus styles, department text and demo disclaimer exist. Public catalogue/front page is configured.
- **Verified records:** 6 campus-labelled categories and cohorts; 36 campus course offerings (six titles repeated across six demo campuses); 30 fictional learners, six educators, two training administrators and one training manager plus Moodle's original admin/guest accounts (41 non-deleted users); 216 enrolments; 36 core Assignments and 36 core Pages; 108 course modules total; 72 grade items. Accounts and all learning content are synthetic. These are record/configuration checks, not workflow acceptance.
- **Verified missing:** 0 quiz records, 0 grade records, 0 course completion records; no `attendance`, `certificate` or `customcert` tables/plugins. No sample assignment submissions/marks, completion evidence, certificates, attendance, event/announcement data or dedicated management report views are evidenced in the existing project notes.
- **Important mismatch:** current catalogue is Foundations of Nursing Practice, Anatomy and Physiology for Nursing, Community and Primary Health Care, Maternal and Child Health, Medical-Surgical Nursing, and Clinical Skills: Patient Observation. Requested demonstration catalogue adds Infection Prevention and Control and Emergency Nursing and Basic Life Support; those must be reconciled into the six-course plan. No infection control flagship exists yet.
- **Runtime risk:** `C:\xampp8.5\php\php.exe` reports PHP 8.5.12. Existing local installation notes say Moodle 5.3 requires PHP 8.3+ and treats PHP 8.5 support as in development. Local dev server/runtime is not production-ready evidence.
- **Proposal/reference gap:** no attached proposal or separate branding reference was present in the accessible project files. Existing logo is present; institutional naming/official-campus assertions and proposal clause mappings remain **Needs verification**. Six campus names are explicitly fictional demo placeholders, not verified institutions.
- **Testing status:** HTTP availability and database state were inspected. Learner/lecturer workflows, browser screenshots, responsive and accessibility checks were not run. Git status could not be inspected because Git rejected this checkout's ownership as unsafe; no global Git setting was changed.

### Verified completed functionality

Only baseline artifacts/configuration listed above are marked verified-present; no end-to-end product journey is marked complete. Evidence is in `docs/echealth-installation.md`, `docs/echealth-implementation-plan.md`, `docs/echealth-compliance-matrix.md`, `public/version.php`, `public/theme/echealth/`, `scripts/echealth_seed_demo.php`, `scripts/echealth_complete_demo.php`, and read-only local runtime/database inspection.

### Missing functionality and critical blockers

1. Obtain the actual proposal and approved identity/branding references before claiming proposal compliance or official institutional identity.
2. Build and validate the flagship Infection Prevention and Control learning journey; it currently has no course record among the six seeded titles.
3. Create quiz, question bank, submissions, grading, progress and completion evidence, then execute both learner and educator journeys.
4. Confirm six demo campus labels with stakeholders or keep them clearly fictional; categories/cohorts do not provide tenant isolation.
5. Select/verify version-compatible attendance and certificate plugins only if needed for the demonstration; none are installed.
6. Resolve supported PHP runtime, HTTPS, backups, operational controls and privacy/security review before any production claim.

### Effort summary

Estimates are rough developer-days for implementation and verification, excluding procurement, clinical content authoring approvals and external infrastructure lead time. Overlapping effort is not double-counted; total remaining effort estimate: **36–55 developer-days**. P0 subtotal: **18–27 days**.

| Workstream | Effort | Priority |
|---|---:|---|
| Branding/theme and UX | 2–4 d | P0 |
| Navigation and role dashboards | 2–4 d | P0/P1 |
| Roles, organisation and demo data | 1–3 d | P0/P1 |
| Course catalogue and flagship course | 8–12 d | P0 |
| Assessment, gradebook and completion | 4–6 d | P0 |
| Attendance and certificates | 2–4 d | P1, conditional |
| Reporting and communications | 2–4 d | P1 |
| Security, infrastructure and privacy | 4–7 d | P0/P1 |
| Accessibility and responsive validation | 2–4 d | P0 |
| Testing and walkthrough/presentation | 5–7 d | P0 |
| **Total** | **32–55 d** | |

### Key dependencies and technical risks

- Proposal and identity approval; clinician/content owner review of all clinical-adjacent materials.
- Supported Moodle/PHP/database matrix and disposable staging database before plugin selection or demo data changes.
- Sample enrolments and role scoping before reporting checks; completion settings before certificate eligibility.
- Third-party plugin compatibility and maintenance burden; non-isolated categories/cohorts; unverified privacy and security controls; PHP 8.5 compatibility status; no measured capacity or SLA evidence.
- Existing seeder scripts are one-time local operations and guard the local database. Do not rerun against a populated site or production.

### Recommended implementation order and milestones

1. **Milestone 1 — Foundation (P0, 4–7 d):** validate source materials, runtime target, theme/layout, navigation, role baseline. Exit: approved visual checklist, no theme regression, role access expectations documented.
2. **Milestone 2 — Functional Learning (P0, 10–15 d):** create/align six course catalogue entries and finish Infection Prevention and Control with quiz, assignment/rubric, gradebook, completion. Exit: clean learner-to-educator journey passes on disposable test data.
3. **Milestone 3 — Institutional Demonstration (P1, 5–8 d):** confirm fictional campus model, scoped cohorts/roles, useful reports, conditional certificate/attendance. Exit: each account sees only authorized records and numbers reconcile.
4. **Milestone 4 — Validation (P0/P1, 7–11 d):** accessibility, responsive, permissions, security, backup/restore and regression review. Exit: no open demo-blocking defects; evidence recorded.
5. **Milestone 5 — Presentation Readiness (P0, 3–5 d):** finalize scripts, limitations, screenshots, credentials handling and rehearsal. Exit: all Go criteria in `docs/DEMO_READINESS.md` pass.

### Demo readiness criteria

Go only when the site is reachable on the chosen demo host, demo accounts work, identity is correctly represented, flagship course journey and lecturer grading pass, roles/reporting show correct authorized data, no broken links or visible debug errors remain, backup/restore fallback exists, known limitations are explicit, and rehearsal has passed. See `docs/DEMO_READINESS.md`.

### Workstream 1 — Government branding and theme

#### [P0] TASK-001 — Approve identity assets and audit theme across templates
- **Status:** Partial
- **Workstream:** Theme
- **Description:** Validate the logo against approved source and finish visual QA without changing the supplied mark.
- **Existing state:** Boost child theme, supplied `public/theme/echealth/pix/ec-health-logo.png` (17,465 bytes), masthead/navbar/footer templates, SCSS palette and responsive rules exist. Source reference/approval and screenshot QA are missing.
- **Implementation steps:**
  - [x] Confirm existing theme inheritance, asset, palette and responsive breakpoints from source.
  - [ ] Obtain approved logo/brand references; compare exact logo pixels and institutional name.
  - [ ] Review header/footer, forms, buttons, cards, tables, badges, breadcrumbs and Moodle admin/course layouts at desktop/tablet/mobile; preserve core templates' behavior.
  - [ ] Capture baseline screenshots and fix only verified defects in child theme/templates.
  - [ ] Check contrast, focus, hover/active and long-name wrapping; retain black/white/green/gold/red/blue only.
- **Relevant files:** `public/theme/echealth/config.php`, `version.php`, `scss/echealth.scss`, `templates/theme_boost/navbar.mustache`, `templates/theme_boost/footer.mustache`, `templates/theme_boost/login.mustache`, `pix/ec-health-logo.png`.
- **Dependencies:** Approved identity source; TASK-021 visual QA.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** Approved logo is visually pixel-identical; no clipping/overlap at tested widths; contrast and keyboard focus meet chosen WCAG 2.2 AA review criteria; core course/admin workflows retain controls.
- **Verification:** Screenshot comparison at 1440, 768 and 375 CSS px; keyboard walkthrough; contrast measurement; check image dimensions/hash against approved source.
- **Evidence:** Theme files above; accessibility and approval evidence pending.
- **Risk:** Medium.

### Workstream 2 — Navigation and dashboard UX

#### [P0] TASK-002 — Validate homepage, dashboard and primary navigation
- **Status:** Partial
- **Workstream:** UX
- **Description:** Separate public catalogue, authenticated dashboard and My Courses purpose; verify layout and navigation with Moodle roles.
- **Existing state:** Front-page catalogue/search and logged-in category/course settings configured; theme widens frontpage/dashboard container and hides duplicate front-page heading. Real user journey unverified.
- **Implementation steps:**
  - [x] Inspect theme CSS and site front-page configuration evidence.
  - [ ] Walk student, educator and manager menu paths; identify duplicate headings, empty states, excessive whitespace and contextual admin links.
  - [ ] Confirm course discovery/search and course cards at mobile/tablet widths.
  - [ ] Document Moodle-native controls that must remain visible in editing mode.
  - [ ] Make minimal child-theme/config changes for reproduced defects and capture before/after evidence.
- **Relevant files:** `public/theme/echealth/scss/echealth.scss`, `templates/theme_boost/navbar.mustache`; site settings in local database.
- **Dependencies:** TASK-001; working role accounts.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** Each role can reach its dashboard, courses and permitted actions in at most three predictable navigation steps; public pages remain distinct from authenticated pages; no horizontal overflow at tested widths.
- **Verification:** Scripted manual navigation for student, editing teacher and manager; screenshots.
- **Evidence:** SCSS and site configuration exist; role journey evidence pending.
- **Risk:** Medium.

### Workstream 3 — Role-based dashboards

#### [P1] TASK-003 — Configure and validate role-specific dashboard blocks
- **Status:** Not started
- **Workstream:** Dashboards
- **Description:** Use native Moodle dashboard blocks/permissions for learner, educator and manager views; avoid misleading fabricated statistics.
- **Existing state:** Native My dashboard exists; custom role dashboard layouts and scoped reporting are not evidenced.
- **Implementation steps:**
  - [ ] Define useful native blocks for courses/progress/deadlines/grades for learners; grading queue/participants/progress for educators; authorized course/cohort/report links for administrators/managers.
  - [ ] Configure blocks and defaults by role where supported; label unavailable attendance/certificate data as absent, never zero-performance claims.
  - [ ] Test empty, loading, error and populated states with demo accounts.
  - [ ] Record capability boundary for cross-campus manager reporting.
- **Relevant files:** Moodle dashboard/site configuration; `public/my/`, `public/blocks/` are core and should not be modified.
- **Dependencies:** TASK-002, TASK-006, TASK-010.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** Each persona sees relevant native actions/data only; dashboard contains no fabricated metrics; educator grading queue links to authorized courses.
- **Verification:** Role-based screenshots and capability checks.
- **Evidence:** No custom dashboard config evidenced.
- **Risk:** Medium.

### Workstream 4 — Organisational and campus configuration

#### [P1] TASK-004 — Confirm demo campus model and document Moodle tenancy limits
- **Status:** Partial
- **Workstream:** Organisation
- **Description:** Decide whether six labels remain illustrative or are replaced by verified institutions; configure category/cohort/group roles accordingly.
- **Existing state:** Six fictional campus categories/cohorts seeded. Categories/cohorts are organizational conveniences and do not isolate campus data as tenants. Current manager role assignments are system-context per seed script.
- **Implementation steps:**
  - [ ] Obtain authoritative campus names/ownership from project sponsor; retain explicit fictional labels until confirmed.
  - [ ] Map each course, cohort, editing teacher and campus administrator to required context.
  - [ ] Reduce broad system-level manager permissions if the demo model requires campus-scoped administration; use category context and test capability inheritance.
  - [ ] Document data visibility boundary and prohibit tenant-isolation claims.
  - [ ] Reconcile six requested course titles with current six seeded titles and whether six titles should be offered in each category.
- **Relevant files:** `scripts/echealth_seed_demo.php`, `scripts/echealth_complete_demo.php`; categories/cohorts in database.
- **Dependencies:** Stakeholder naming decision; TASK-006 role matrix.
- **Estimated effort:** 1–3 d.
- **Acceptance criteria:** Every campus/course/account is visibly marked fictional unless verified; test accounts cannot access unauthorized course activities; architecture states category/cohort limitations.
- **Verification:** Capability tests as learner, educator, campus admin and manager; enrolment/category inventory.
- **Evidence:** Seed arrays and 6 category/6 cohort records; security scoping unverified.
- **Risk:** High.

### Workstream 5 — Nursing course catalogue

#### [P0] TASK-005 — Align six-course catalogue with required demonstration scope
- **Status:** Partial
- **Workstream:** Courses
- **Description:** Reconcile the existing six seeded subjects with the requested catalogue: Fundamentals of Nursing Practice; Infection Prevention and Control; Clinical Skills and Patient Assessment; Emergency Nursing and Basic Life Support; Maternal and Child Health; Healthcare Ethics and Professional Conduct. Complete consistent descriptions, outcomes, topic structure, core Page/Book resources and course image strategy.
- **Existing state:** Six seeded titles differ from requested list; 36 course offerings each have four sections, one core Moodle Page and one core Assignment. Material is explicitly illustrative, not clinical guidance.
- **Implementation steps:**
  - [ ] Approve titles/codes and reconcile duplicates/current subjects without deleting seeded evidence until database backup and migration plan exist.
  - [ ] For every approved title, specify code, description, thumbnail, outcomes, topic sequence, Page/Book materials, discussion forum, quiz, assignment/rubric, completion rules, gradebook setup, educator access and learner testing.
  - [ ] For each agreed subject, define description, learning outcomes, module sequence, accessible resources/media, forum, quiz, Assignment, rubric, activity/course completion and educator access.
  - [ ] Add approved thumbnail assets with alternative text; avoid unlicensed patient imagery.
  - [ ] Review all content with qualified clinical educator; label fictional demonstration material and link to current approved policy only when authorized.
  - [ ] Validate learner view and gradebook for each final course offering or a documented representative subset.
- **Relevant files:** `scripts/echealth_seed_demo.php`, `scripts/echealth_add_learning_pages.php`, `scripts/echealth_add_assessments.php`.
- **Dependencies:** Course scope and clinical content review; TASK-007 flagship.
- **Estimated effort:** 3–5 d plus content-owner time.
- **Acceptance criteria:** Six approved course subjects exist with stated outcomes, accessible resources, activity plan and no unsupported clinical instruction; offerings match the approved campus model.
- **Verification:** Course inventory export and educator/learner review checklist.
- **Evidence:** Seed catalogue in `scripts/echealth_seed_demo.php`; existing activity counts in summary.
- **Risk:** High.

### Workstream 6 — Flagship Infection Prevention and Control

#### [P0] TASK-006 — Build complete Infection Prevention and Control flagship journey
- **Status:** Not started
- **Workstream:** Courses
- **Description:** Deliver one working, educator-reviewed flagship course before broad superficial content population.
- **Existing state:** No Infection Prevention and Control title/course found in seed catalogue; no quizzes or completions exist. Current title is Foundations of Nursing Practice.
- **Implementation steps:**
  - [ ] Create course title/code, overview, outcomes and five modules with clear prerequisite/progress sequence.
  - [ ] Propose five educator-reviewed modules (orientation and scope; transmission and risk; precautions and protective practices; environmental/equipment practices; reflection and knowledge review); clinical owner must approve all clinical details before learner release.
  - [ ] Add introductory content, Page/Book learning resources, accessible embedded media where approved and low-risk knowledge checks/forums.
  - [ ] Create 15 reviewed questions in course-context question bank categories; configure attempts, feedback and configurable passing grade.
  - [ ] Create assignment submission with clear fictional case prompt, file/text rules, rubric, feedback workflow and grade item.
  - [ ] Configure activity completion and course completion criteria, including passing quiz and graded assignment.
  - [ ] Add certificate only if TASK-012 compatibility and eligibility checks pass.
  - [ ] Run student enrolment → modules → activities → quiz → assignment → educator grading → completion record/certificate journey on disposable accounts.
- **Relevant files:** Use existing seeder conventions in `scripts/echealth_seed_demo.php`, `scripts/echealth_add_learning_pages.php`, `scripts/echealth_add_assessments.php`; course records/activities in Moodle DB. Do not change Moodle core.
- **Dependencies:** TASK-005, clinical review, TASK-008, optional TASK-012.
- **Estimated effort:** 5–7 d plus clinical review.
- **Acceptance criteria:** One student finishes all five modules, submits assignment, receives educator feedback, passes 15-question quiz at configured threshold, satisfies completion rules and sees a reliable completion record; certificate only claimed if tested.
- **Verification:** Record IDs, attempt/grade/completion evidence and walkthrough recording/screenshots; repeat as educator.
- **Evidence:** No course, quiz or completion evidence currently; core Page/Assignment patterns available.
- **Risk:** High.

### Workstream 7 — Assessment and competencies

#### [P0] TASK-007 — Configure quiz, question bank, rubric and gradebook workflow
- **Status:** Partial
- **Workstream:** Assessment
- **Description:** Use Moodle core Quiz, Assignment, rubric and gradebook; separate native capability from configured evidence.
- **Existing state:** Core Quiz and Assignment installed; 36 Assignments and 72 grade items exist; 0 quiz records and 0 grade records. Rubric use and completion criteria unverified.
- **Implementation steps:**
  - [x] Confirm core Quiz, Assignment and gradebook components are present in source/database.
  - [ ] Set quiz attempt/retake, time, review and pass threshold with course owner.
  - [ ] Create question categories and 15 flagship questions; verify question bank access and shuffled/selected question behavior.
  - [ ] Configure rubric, late submission policy, educator feedback and grade visibility.
  - [ ] Set course completion prerequisites; test pass/fail, retake and missing-submission states.
  - [ ] Describe Competencies/Learning Plans as optional native features requiring framework configuration; no clinical placement system proposed.
- **Relevant files:** Moodle core `public/mod/quiz/`, `public/mod/assign/`, `public/grade/`, `public/competency/` (configuration/data only; no core edits).
- **Dependencies:** TASK-006.
- **Estimated effort:** 2–4 d.
- **Acceptance criteria:** Learner and educator results match configured grading policy; retake, rubric, visibility, completion and feedback behavior are evidenced.
- **Verification:** Test users exercise fail/pass/retake/submission/mark/review; reconcile gradebook export.
- **Evidence:** 36 assignments, 72 grade items; 0 quiz and grade records.
- **Risk:** High.

### Workstream 8 — Attendance and certification

#### [P1] TASK-008 — Select and validate attendance/certificate approach
- **Status:** Not started
- **Workstream:** Plugins
- **Description:** Decide if these capabilities are necessary for demo; if so, select plugins compatible with exact Moodle/PHP target and maintained security posture.
- **Existing state:** No attendance/certificate plugin tables/components detected. Core badges exist but are not equivalent to a downloadable certificate or attendance register.
- **Implementation steps:**
  - [ ] Compare current supported plugin releases to target Moodle branch, PHP, DB, licensing, accessibility, maintenance and backup/restore requirements.
  - [ ] Do not install plugins in planning; test candidate only in disposable staging after approval.
  - [ ] Configure sample session and lecturer attendance entry; verify learner visibility and supported export.
  - [ ] Configure certificate template and eligibility linked to completion; test download, name and issue timing.
  - [ ] Document native alternative/manual fallback and plugin limitations.
- **Relevant files:** Plugin inventory under `public/mod`, `public/report`, `public/enrol`; no candidate plugin path currently exists.
- **Dependencies:** Supported runtime target, TASK-006/007 completion rules.
- **Estimated effort:** 2–4 d, conditional.
- **Acceptance criteria:** Feature omitted from demo unless plugin passes compatibility, permission, export and restore checks; any certificate issued only after verified completion.
- **Verification:** Compatibility report and role-based session/certificate walkthrough in staging.
- **Evidence:** Attendance/certificate DB tables unavailable; no third-party plugins documented.
- **Risk:** High.

### Workstream 9 — Demonstration users and data

#### [P0] TASK-009 — Validate synthetic roster and safe credential handling
- **Status:** Partial
- **Workstream:** Demo data
- **Description:** Preserve synthetic-only account population, verify roster-to-campus enrolments and prepare safe, separate credential sheet.
- **Existing state:** 30 learners, six educators, two training administrators, one training manager and 216 enrolments. Seed scripts generate random passwords stored under local dataroot, outside web root, with restrictive permissions.
- **Implementation steps:**
  - [x] Inspect count and seed script's localhost/database/dataroot guard.
  - [ ] Reconcile account roles and campus enrolments against approved demo matrix; validate no cross-course leakage.
  - [ ] Reconfirm fictional names and reserved `.test` email; remove any unnecessary personal fields.
  - [ ] Generate controlled demo credential handout through approved secure channel; never put passwords in repository/docs/screenshots.
  - [ ] Snapshot database and dataroot after test data is ready; define reset/restore method.
- **Relevant files:** `scripts/echealth_seed_demo.php`, `scripts/echealth_complete_demo.php`, `docs/echealth-installation.md`.
- **Dependencies:** TASK-004, TASK-006.
- **Estimated effort:** 1 d.
- **Acceptance criteria:** Counts and enrolments reconcile, all accounts fictional, minimum permissions demonstrated, credentials are not committed, reset procedure restores clean state.
- **Verification:** Role/enrolment report; secret scan of deliverables; backup restore rehearsal.
- **Evidence:** Runtime counts above; scripts write credentials outside web root.
- **Risk:** High.

### Workstream 10 — Reporting and analytics

#### [P1] TASK-010 — Configure honest training reports and exports
- **Status:** Not started
- **Workstream:** Reporting
- **Description:** Build role-authorized views using Moodle reports/gradebook/completion reports; only show metrics backed by records.
- **Existing state:** Core report plugins exist, but no demo report view is evidenced and no grades/completions exist.
- **Implementation steps:**
  - [ ] Identify site/course reports available on this exact build and least-privilege audiences.
  - [ ] Seed representative synthetic activity/grade/completion records only after test cases define expected totals.
  - [ ] Configure course-level participation/completion and assessment exports using native reports where adequate.
  - [ ] Test CSV/download access, data minimization and totals against source records.
  - [ ] Document limitations for cross-campus rollups and regulatory formats.
- **Relevant files:** `public/report/`, `public/grade/report/`, `public/completion/`; no custom reports evidenced.
- **Dependencies:** TASK-004, TASK-007, TASK-009.
- **Estimated effort:** 2–3 d.
- **Acceptance criteria:** Report totals reconcile to test records; roles see only authorized scope; exports open and disclose required fields only.
- **Verification:** Role-based report review and independent count reconciliation.
- **Evidence:** Native report directories present; no grades/completions or report views.
- **Risk:** High.

### Workstream 11 — Communication and notifications

#### [P1] TASK-011 — Configure demo announcements, calendar and email expectations
- **Status:** Partial
- **Workstream:** Communication
- **Description:** Configure native forum/announcement/calendar and notification behavior consistent with local demo email transport.
- **Existing state:** Core Moodle features present; no seeded events or announcements evidenced. Seeder sets no-email mode.
- **Implementation steps:**
  - [ ] Choose a course announcement and dated activity deadline for fictional demo course.
  - [ ] Define learner/educator message expectations and notification preferences.
  - [ ] Verify outgoing email transport in a safe test mailbox or clearly document that email delivery is disabled.
  - [ ] Check calendar timezone (Africa/Johannesburg) and duplicate/deadline behavior.
  - [ ] Exclude custom SMS unless separately approved.
- **Relevant files:** Moodle core `public/calendar/`, `public/mod/forum/`; seed scripts set `$CFG->noemailever` for CLI.
- **Dependencies:** Demo date and SMTP decision; TASK-009.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** Announcements/calendar render for intended roles; delivery claims match tested transport; no external messages sent unintentionally.
- **Verification:** Account preference check, calendar view, controlled test message.
- **Evidence:** Core features exist; no event/announcement records reported.
- **Risk:** Medium.

### Workstream 12 — Security, privacy and permissions

#### [P0] TASK-012 — Review authentication, capability scope and privacy controls
- **Status:** Partial
- **Workstream:** Security
- **Description:** Evidence technical controls without claiming legal compliance or certification.
- **Existing state:** Synthetic local accounts; no production HTTPS/security review documented. Training administrators and manager assigned built-in manager role at system context per script.
- **Implementation steps:**
  - [ ] Inventory authentication, password/session, guest access, self-registration and file upload settings.
  - [ ] Test student/educator/administrator least privilege and data visibility; narrow system-level manager assignments if unnecessary.
  - [ ] Verify HTTPS, secure dataroot separation, debug display disabled for demo, upload restrictions and audit logging on target host.
  - [ ] Document POPIA design considerations, data minimization, retention/deletion, breach process and institutional DPO/security decisions as open obligations.
  - [ ] Review installed plugin advisories and backup credential/data protection.
- **Relevant files:** `config.php` (do not disclose secrets), `public/admin/`, `public/login/`, `scripts/echealth_complete_demo.php`; environment controls.
- **Dependencies:** Hosting target; TASK-004/TASK-009.
- **Estimated effort:** 2–4 d.
- **Acceptance criteria:** Written role matrix passes negative access tests; no production/secrecy or certification claim without independent evidence; HTTPS and dataroot controls verified on target.
- **Verification:** Capability matrix, configuration review, security scan/manual tests.
- **Evidence:** Local config uses `http://localhost:8080`; exact production controls not verified.
- **Risk:** High.

### Workstream 13 — Performance and infrastructure

#### [P1] TASK-013 — Prepare supported hosting, cron, backup and recovery plan
- **Status:** Partial
- **Workstream:** Infrastructure
- **Description:** Separate local demonstration from production deployment; validate target requirements, operations and recovery.
- **Existing state:** Local MariaDB and PHP 8.5.12; Moodle 5.3+; site served locally. No measured capacity, cron, monitor, backup or restore evidence.
- **Implementation steps:**
  - [ ] Select supported PHP/database/OS combination for target Moodle release; resolve PHP 8.5 in-development status.
  - [ ] Define webroot `public/`, private dataroot, DB, HTTPS, outbound mail, scheduled cron and cache/session design.
  - [ ] Define backup cadence/retention/encryption and rehearse database+dataroot restore.
  - [ ] Set monitoring/error logging and upgrade/plugin review process.
  - [ ] Measure load against proposal targets only after proposal access; state as targets until tested.
- **Relevant files:** `config.php`, `docs/echealth-installation.md`, `composer.json`, `package.json`; local deployment command in existing notes.
- **Dependencies:** Hosting decision, proposal source.
- **Estimated effort:** 2–4 d for plan/demo hardening; infrastructure provisioning excluded.
- **Acceptance criteria:** Supported runtime is confirmed; cron runs; backup restore succeeds; no SLA/performance statement without measured result.
- **Verification:** Environment report, cron log, restore evidence and load result.
- **Evidence:** Existing installation notes; no deployment/Docker files discovered in root inventory.
- **Risk:** High.

### Workstream 14 — Accessibility and responsive design

#### [P0] TASK-014 — Validate accessible mobile and desktop experiences
- **Status:** Not started
- **Workstream:** Accessibility
- **Description:** Test Moodle core + child theme across devices and learning activities; document defects and remediation.
- **Existing state:** Responsive CSS, visible focus and reduced-motion rule present; no independent QA evidence.
- **Implementation steps:**
  - [ ] Test desktop/tablet/mobile navigation, cards, tables, long course titles and quiz/assignment controls.
  - [ ] Perform keyboard-only and screen-reader semantic review of login, navigation, course page, quiz and submission.
  - [ ] Check labels, validation, image alt text, focus order, zoom/reflow, contrast and reduced-motion.
  - [ ] Record issue, severity, reproduction, owner and retest evidence; remediate P0 blockers.
- **Relevant files:** `public/theme/echealth/scss/echealth.scss`, relevant Mustache templates and course content.
- **Dependencies:** TASK-001, TASK-006.
- **Estimated effort:** 2–4 d.
- **Acceptance criteria:** No critical keyboard/accessibility barrier in demo journeys; all found defects logged; chosen WCAG 2.2 AA criteria and scope recorded.
- **Verification:** Manual assistive-technology check plus automated scan as a supplement; screenshots.
- **Evidence:** Responsive styles exist; test results absent.
- **Risk:** High.

### Workstream 15 — Automated and manual testing

#### [P0] TASK-015 — Define and execute Moodle-specific regression and end-to-end checks
- **Status:** Not started
- **Workstream:** Testing
- **Description:** Validate seeded experience on staging with repeatable acceptance checks.
- **Existing state:** PHPUnit/Behat config and core suites exist in Moodle source; no project-specific journey tests or demo acceptance evidence.
- **Implementation steps:**
  - [ ] Identify appropriate project-owned theme checks and installed Moodle CLI/test configuration; do not edit Moodle core tests.
  - [ ] Check theme PHP/template/SCSS build and plugin compatibility against target release.
  - [ ] Manually test enrolment, role access, quiz attempt, submission, grading, grade visibility, completion and exports.
  - [ ] Add Behat/PHPUnit coverage only where stable and project-owned; keep disposable test data isolated.
  - [ ] Run regression, supported browsers, responsive layouts and broken-link checks; record exact build and evidence.
- **Relevant files:** `phpunit.xml.dist`, `public/theme/echealth/`, `package.json`, `Gruntfile.js`, `scripts/`.
- **Dependencies:** TASK-006–014.
- **Estimated effort:** 2–4 d.
- **Acceptance criteria:** Each walkthrough step has pass/fail evidence; no P0 defects, cross-role leakage or unexplained count discrepancies.
- **Verification:** Test record/checklist and run logs; do not treat plugin presence as pass.
- **Evidence:** Test configuration exists; no custom test execution evidence.
- **Risk:** High.

### Workstream 16 — End-to-end demonstration scenarios

#### [P0] TASK-016 — Run student, lecturer, campus admin, management and technical scenarios
- **Status:** Not started
- **Workstream:** Demo journeys
- **Description:** Execute and refine all scripts in `docs/DEMO_WALKTHROUGH.md` with named fictional accounts and fallback paths.
- **Existing state:** No workflow runs evidenced.
- **Implementation steps:**
  - [ ] Execute Student Journey A and capture expected result per step.
  - [ ] Execute Lecturer Journey B including rubric/feedback and quiz review.
  - [ ] Execute Campus Administration Journey C with permitted category/cohort management.
  - [ ] Execute Department Management Journey D with only available, reconciled reports.
  - [ ] Execute Technical Overview E and explain isolation, backups, hosting, constraints and future LMIS boundary.
  - [ ] Record duration, preconditions, test IDs, fallbacks and defects; rehearse once without administrator rescue.
- **Relevant files:** `docs/DEMO_WALKTHROUGH.md`, `docs/ARCHITECTURE.md`, demo credentials stored outside repository.
- **Dependencies:** TASK-003, 006–015.
- **Estimated effort:** 2–3 d.
- **Acceptance criteria:** Each scenario completes within its allotted time or uses disclosed fallback; no real credentials/data exposed; all claims evidenced.
- **Verification:** Rehearsal checklist signed by presenter and observer.
- **Evidence:** Scenario evidence pending.
- **Risk:** High.

### Workstream 17 — Presentation and supporting documentation

#### [P1] TASK-017 — Prepare 20–25 minute presentation pack and demo operator guide
- **Status:** Partial
- **Workstream:** Presentation
- **Description:** Provide business overview, capability boundaries, architecture, security approach, roadmap, support and training notes.
- **Existing state:** Three `docs/echealth-*.md` project notes exist; no approved proposal, screenshot pack or credentials guide.
- **Implementation steps:**
  - [ ] Reconcile supplied proposal statements when obtained; distinguish confirmed requirements from assumptions.
  - [ ] Prepare 20–25 minute agenda, role demo account guide (credentials outside repo), screenshots and known limitations.
  - [ ] Include learner, educator, reporting, security/hosting, roadmap, support/training and future LMIS sections.
  - [ ] Review all screenshots for private data, debug output, addresses and secrets.
- **Relevant files:** `docs/echealth-installation.md`, `docs/echealth-implementation-plan.md`, new architecture/walkthrough/matrix/readiness docs.
- **Dependencies:** TASK-016, proposal source.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** Presenter can deliver timed agenda and answer each capability/limitation with evidence; no secrets or private data in artifacts.
- **Verification:** Timed rehearsal and artifact review.
- **Evidence:** Existing implementation docs; presentation pack absent.
- **Risk:** Medium.

### Workstream 18 — Functional compliance matrix

#### [P0] TASK-018 — Reconcile requirements to supplied proposal and Moodle evidence
- **Status:** Blocked
- **Workstream:** Compliance
- **Description:** Map exact proposal requirement IDs and wording; do not infer Gauteng procurement requirements as Eastern Cape commitments.
- **Existing state:** Existing `docs/echealth-compliance-matrix.md` is a useful high-level matrix, but no proposal attachment was accessible. New matrix records current evidence and placeholder requirement groups.
- **Implementation steps:**
  - [ ] Obtain proposal/tender and approved scope/version; record provenance and date.
  - [ ] Populate requirement reference/description, Moodle capability classification, implementation status, work, verification, evidence and limitations in `docs/FEATURE_COMPLIANCE_MATRIX.md`.
  - [x] Record current requirement areas: learning, academics, assessment, attendance, certificates, communications, reporting, registration, admissions, placements, finance, regulatory, authentication, security, hosting and support.
  - [ ] Have project owner approve unsupported/conditional claims and explicitly identify Gauteng-specific clauses.
- **Relevant files:** `docs/echealth-compliance-matrix.md`, `docs/FEATURE_COMPLIANCE_MATRIX.md`.
- **Dependencies:** Actual proposal; project owner.
- **Estimated effort:** 1–2 d after source received.
- **Acceptance criteria:** Every proposal clause has traceable reference and evidence/limitation; no inferred clauses labeled as proposal requirement.
- **Verification:** Clause-by-clause review with source open.
- **Evidence:** No proposal/reference file found in repo; existing matrix lists boundaries.
- **Risk:** High.

### Workstream 19 — Release and demo readiness

#### [P0] TASK-019 — Satisfy Go/No-Go checklist and preserve rollback path
- **Status:** Not started
- **Workstream:** Release readiness
- **Description:** Complete `docs/DEMO_READINESS.md`, ensure known limits and fallback plan, and make a final demo decision based on evidence.
- **Existing state:** Local site is HTTP 200; workflows, backup restore, screenshot capture and rehearsal are outstanding.
- **Implementation steps:**
  - [ ] Verify target development/demo environment, theme, logo, navigation and demo accounts.
  - [ ] Test student and lecturer journeys, reports, roles and optional certificate.
  - [ ] Check broken links, visible debugging errors, screenshots, backup/restore and rollback operator instructions.
  - [ ] Record known limitations, presenter, date/build, evidence location and Go/No-Go decision.
- **Relevant files:** `docs/DEMO_READINESS.md`, `docs/DEMO_WALKTHROUGH.md`, `docs/echealth-installation.md`.
- **Dependencies:** TASK-015–018.
- **Estimated effort:** 1–2 d.
- **Acceptance criteria:** All critical Go criteria pass; any missing essential workflow or role failure is No-Go; conditional features are omitted or fallback disclosed.
- **Verification:** Completed readiness checklist and rehearsal sign-off.
- **Evidence:** Site HTTP 200 only; no demo readiness claim.
- **Risk:** High.

## Parallel work

After identity/runtime owners are assigned, TASK-001/002, TASK-004 organizational discovery, TASK-013 infrastructure review and TASK-018 proposal reconciliation can proceed in parallel. Course content authoring (TASK-005/006) can run alongside infrastructure/security planning, but plugin selection and completion reporting depend on the final Moodle runtime and course criteria. End-to-end and presentation work depend on the flagship journey.

## Task roll-up

There are **19 master tasks** in this plan: **12 P0**, **7 P1**, **1 blocked** (TASK-018 is also priority P0). Zero master tasks are verified complete as implementation outcomes; verified baseline subtasks are checked and evidenced above. Estimated remaining effort is **32–55 developer-days** by workstream (overlapping tasks are not added twice). Detailed evidence and acceptance gates appear under each task and in the companion documents.

### Navigation UI kit prompts — 26 October 2026

- [x] Implemented the child-theme navigation and dashboard in `public/theme/echealth/`, preserving Moodle core, Boost, the existing logo, and native course pages.
- [x] Added capability-aware administration links, real account/course/calendar/announcement data, honest empty states, responsive course cards, supplied local SVG/WebP assets, configurable portal branding, and keyboard-aware Boost drawer handling.
- [x] Purged Moodle caches, visually checked the authenticated desktop dashboard, linted theme PHP and parsed the AMD JavaScript source/build.
- [x] Updated `docs/NAVIGATION_UI_QA.md` with evidence and remaining acceptance checks.
- [ ] Role-specific student/lecturer/coordinator checks, data-rich scenarios, mobile/responsive screenshots and full assistive-technology QA still require a suitable test account/device/data.

