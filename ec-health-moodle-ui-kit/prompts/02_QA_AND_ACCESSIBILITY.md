# CODEX — QA the Eastern Cape Health Moodle dashboard rollout

Inspect and test the implementation against `prompts/01_IMPLEMENT_NAVIGATION.md`, screenshot reference and the attached assets. Do not assert success without checking.

## Visual and responsive checks
- 1440px/1024px/768px/390px widths: header/logo, sidebar/drawer, grids, cards, tables, footer; no large wasted left gutter or horizontal overflow.
- Compare palette/logo to the user screenshots. Check contrast, layout shift, long course names, multiline navigation, SVG strokes and hover/active/focus states.

## Role and navigation checks
- Anonymous site home and sign-in; authenticated student, lecturer, coordinator/admin dashboard and My Courses; all real navigation targets.
- Restricted items not visible to unauthorised roles; direct URL permission protections remain in force.
- Editing mode, course settings, question bank, gradebook, certificates, notifications and support route remain reachable if enabled.
- Mobile menu: open/close, focus trapping, Escape, overlay, restored focus, aria-expanded.

## Functional checks
- Courses from database, enrolment data, pending grading and upcoming activity dates; correct empty states without fabricated statistics.
- Search performs real action; filters are not decorative.
- No external image CDN needed for the packaged thumbnails.
- Verify 40+ icon files and six WebP/JPEG thumbnails referenced in the kit exist.
- Build/test against installed Moodle version; run Moodle-native tests where available.

## Output
Write `docs/NAVIGATION_UI_QA.md`: findings, issue severity, files changed, proof/test evidence, role screenshots, unresolved items, and clear pass/fail checklist. Fix P0 and P1 defects before declaring demo-ready.
