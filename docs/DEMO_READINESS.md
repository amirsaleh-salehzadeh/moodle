# Demo readiness and release checklist

**Current decision: NO-GO for a live functional demonstration.** The local Moodle site responds, but the flagship course, assessments, grades, completions, permissions/workflows, backup restore and rehearsal have not been evidenced end to end.

Use `[x]` only after evidence is recorded. A configured file/plugin alone is not a pass.

## Environment and identity

- [x] Local site responds at `http://localhost:8080` (HTTP 200 observed on 26 October 2026).
- [x] Moodle reports 5.3+ branch 503; `theme_echealth` is active by configuration inspection.
- [ ] Approved demonstration host selected; TLS and trusted URL verified.
- [ ] Supported PHP/database runtime confirmed; local PHP 8.5.12 development caveat resolved for host.
- [ ] Theme and unchanged official logo checked against approved reference at desktop/tablet/mobile sizes.
- [ ] Front page, login, dashboard, catalogue, course, admin and footer screenshots reviewed; no duplicate headings, clipping or broken layout.
- [ ] No visible Moodle debug warnings/notices or fatal errors during each journey.

## Learning journeys and data

- [ ] Six final course subjects approved and catalogue reconciled with existing six seed titles.
- [ ] Infection Prevention and Control flagship has five reviewed modules and 15 question assessment.
- [ ] Student can access material, attempt quiz, view result, submit assignment and see feedback.
- [ ] Lecturer can view assigned participants, apply rubric, mark submission and review quiz/progress.
- [ ] Passing, failing, retake, missing submission and completion states tested.
- [ ] Completion record is correct; certificate is shown only if compatible plugin was installed and generation/download tested.
- [ ] 30 fictional learners, six educators, two administrators and one manager account counts/roles verified; no real learner/patient data.
- [ ] Demo credentials validated securely and not present in repository, screenshots or presentation.

## Roles and reporting

- [ ] Student cannot access another learner's private data or educator controls.
- [ ] Educator can grade only assigned course(s).
- [ ] Campus administrator has only approved category/course scope; cross-campus negative access test passes.
- [ ] Training manager reporting scope is explicitly approved and tested.
- [ ] Report totals reconcile to synthetic source records; export opens and contains minimum necessary fields.
- [ ] Campus names are verified or labeled fictional; no tenant isolation claim from Moodle categories/cohorts.

## Communication, accessibility and quality

- [ ] Announcements/calendar and notification behavior tested; email delivery claims match SMTP evidence.
- [ ] No broken links in walkthrough path.
- [ ] Keyboard-only path, visible focus, form labels/errors, image alt text and screen-reader semantics reviewed.
- [ ] Mobile/tablet layouts and quiz/assignment interactions tested; no horizontal overflow.
- [ ] Browser/regression checks recorded for chosen demo browsers.
- [ ] Clinical-adjacent material reviewed and visibly labeled illustrative, not approved clinical guidance.

## Operations and presentation

- [ ] Database and Moodle data directory backup taken after test data ready.
- [ ] Restore approach documented and rehearsed on disposable environment.
- [ ] Rollback/reset operator and time estimate documented; one-time seed scripts are not rerun on populated site.
- [ ] Hosting, privacy, security and known Moodle limitations documented; no unmeasured SLA/performance claims.
- [ ] `docs/DEMO_WALKTHROUGH.md` scenarios rehearsed within 20–25 minutes, including fallback paths.
- [ ] Screenshots and presentation reviewed for secrets, debug information and personal data.
- [ ] Presenter has secure account access and tested logout/session reset procedure.
- [ ] Demo limitations and future LMIS scope are ready to explain.

## Go / No-Go gate

**GO** requires all of the following: reachable approved host; correct approved identity; functional student and lecturer flagship journeys; correct role isolation; reconciled report evidence; no critical accessibility or broken-link defects; no visible debugging errors; backup/restore fallback; secure demo accounts; known limitations disclosed; and successful timed rehearsal.

**NO-GO** if any essential learner/educator workflow fails, an account crosses its authorized scope, the course presents unsafe or unapproved clinical claims, credentials/data are exposed, source evidence does not support presented metrics, or a viable restore/fallback is unavailable. Optional attendance/certification can be omitted only if clearly disclosed and the core journey does not promise it.

## Sign-off record (complete before each event)

| Field | Value |
|---|---|
| Event / date / timezone | Pending (use Africa/Johannesburg) |
| Moodle build and host | Pending |
| Presenter / technical operator | Pending |
| Test evidence location | Pending |
| Open P0 defects | Pending |
| Decision (Go / No-Go) | No-Go until required checks pass |
| Approver and timestamp | Pending |
