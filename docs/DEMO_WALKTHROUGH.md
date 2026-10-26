# Eastern Cape Health Moodle demonstration walkthrough

**Status:** Script is planned; no end-to-end scenario has yet been run. Use synthetic accounts only. Credentials live outside the repository in the local Moodle data directory; retrieve them through the approved operator process and never paste passwords into screenshots or notes. Demo content is illustrative, not official clinical guidance.

## Operator setup

- Site: `http://localhost:8080` for the current local proof of concept; use the approved demo host when available.
- Accounts: one learner (`learner.*`), one educator (`educator.*`), one training administrator (`training.admin.1`), and the training manager (`training.manager`). Confirm credentials securely before the session.
- Current blocker: the requested Infection Prevention and Control course, quiz, grades, completions, reports, attendance and certificate are not currently evidenced. Do not present these steps as working until TASK-006 through TASK-016 pass. Use a prepared screenshot or explain the planned behavior as a fallback, clearly labelled.
- Reset: restore the pre-demo database and Moodle data-directory backup; do not rerun one-time seed scripts on an already seeded site.

## Scenario A — Student journey (8–10 minutes)

**Account:** Fictional learner enrolled in the flagship course. **Preconditions:** Course content, quiz, assignment, completion criteria and optional certificate have passed readiness checks. **Test data:** Fresh attempt and clearly fictional assignment response.

1. Sign in at the approved site URL.
2. Open **Dashboard** and confirm enrolled course cards and deadlines.
3. Open **My courses** or catalogue, then select **Infection Prevention and Control**.
4. Review the course overview and five modules; open the introduction and learning resources.
5. Complete a knowledge-check activity and confirm completion indicator.
6. Open the 15-question knowledge assessment, review instructions, attempt it, submit and view grade/feedback.
7. Open the assignment, submit the fictional case reflection using the allowed text/file method, and confirm submission status.
8. Return to course page; review activity progress and any available announcement/calendar event.
9. After educator grading, reopen assignment feedback and verify the mark/rubric.
10. Confirm course completion record; download/view certificate only if installed and eligibility verified.

**Expected result:** The learner can navigate resources, complete activities, see only permitted grades/feedback and meet documented completion criteria. **Duration:** 8–10 min. **Fallback:** Use a pre-captured learner screenshot for an unavailable optional certificate; if quiz/submission/completion fails, stop the journey and declare No-Go rather than imply success.

## Scenario B — Lecturer journey (5–6 minutes)

**Account:** Educator assigned to the course. **Preconditions:** Learner assignment submission and quiz attempt exist in resettable synthetic data.

1. Sign in and open Dashboard; select the assigned flagship course.
2. Open **Participants** and confirm the learner is in the course.
3. Open the assignment grading view and select the synthetic submission.
4. Apply the published rubric, enter constructive fictional feedback and save the grade.
5. Confirm grading status and learner feedback visibility.
6. Open quiz results/statistics and review the learner attempt.
7. Open course progress/completion view and identify any learner action required.

**Expected result:** Lecturer can act only within assigned course(s), grade through the configured rubric, and review quiz/progress data. **Duration:** 5–6 min. **Fallback:** If an optional report is absent, show native activity grading and disclose the report limitation; any access to another campus course is a permission defect and No-Go.

## Scenario C — Campus administration (4–5 minutes)

**Account:** Training administrator only after role scope is approved and tested. **Preconditions:** Confirm campus labels remain explicitly fictional or replace with verified names; test cohort/course records exist.

1. Sign in and open the authorized course category.
2. Show the category and cohort that represent the demo campus.
3. Open the permitted course and review enrolment methods/participants.
4. Enrol a fresh synthetic learner or demonstrate a pre-created test enrolment.
5. Review role assignment at course/category context and confirm restricted administration links.
6. Open an authorized course participation report.

**Expected result:** Administrator can manage only the agreed scope and see only necessary data. **Duration:** 4–5 min. **Fallback:** If category-level isolation is not proven, use a training-manager observer account and explain Moodle category/cohort limitations; do not call this multi-tenant administration.

## Scenario D — Department management (4–5 minutes)

**Account:** Training manager with explicitly approved reporting permissions. **Preconditions:** Representative synthetic activity, grade and completion evidence; reports reconciled to source records.

1. Sign in and open the authorized reporting/dashboard view.
2. Review learner participation and course completion figures.
3. Compare assessment outcomes across the available course scope.
4. Identify a synthetic learner who needs follow-up based on a transparent criterion.
5. Export an available report to CSV and verify totals/columns.
6. State which regulatory reporting formats are not implemented.

**Expected result:** Only measured, permission-scoped records are shown; totals reconcile and export contains no unnecessary personal fields. **Duration:** 4–5 min. **Fallback:** If aggregate reporting is not configured, show a native course report and state that department-wide roll-up requires further design. Never substitute invented metrics.

## Scenario E — Technical overview (3–4 minutes)

**Account:** Presenter/admin in a non-sensitive, read-only view. **Preconditions:** Architecture/runbook documents reviewed; secrets and dataroot paths are not exposed on screen.

1. Describe Moodle 5.3+ and the `public/` web root, MariaDB, private data directory and Boost child theme.
2. Explain role/capability permissions and why category/cohort organization is not tenant isolation.
3. Show the documented backup plus database/data-directory restore procedure without revealing credentials.
4. Explain supported-hosting decision, PHP 8.5 local-runtime caveat and that no SLA has been measured.
5. Explain Moodle boundaries: admissions, finance, placements, payroll/Persal and regulatory integrations are future LMIS scope.
6. Close with the implementation milestones and known limitations.

**Expected result:** Presenter distinguishes installed/configured functions from proposed work and makes no unsupported compliance or service claim. **Duration:** 3–4 min. **Fallback:** Use `docs/ARCHITECTURE.md` diagram if infrastructure screens are not available.

## 20–25 minute agenda

| Segment | Time |
|---|---:|
| Welcome, scope and demo notice | 1 min |
| Student journey | 8 min |
| Lecturer journey | 5 min |
| Campus administration | 4 min |
| Department reporting | 4 min |
| Technical overview and roadmap | 3 min |
| **Total** | **25 min** |

Trim to 20 minutes by shortening course narration and admin reporting to three minutes each; do not skip permission or limitation statements.
