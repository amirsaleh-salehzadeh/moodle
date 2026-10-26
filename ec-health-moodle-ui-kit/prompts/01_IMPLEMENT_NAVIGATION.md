# CODEX — Implement the supplied Eastern Cape Health dashboard/navigation assets

You have an existing Moodle deployment and an attached `ec-health-moodle-ui-kit.zip`. Inspect the repository, installed Moodle version, active custom theme, block regions, renderer/layout conventions, and actual authenticated homepage before editing. Use `ASSET_MANIFEST.json` for exact resource paths. Inspect `preview/dashboard-visual-reference.png` as a **visual reference**, not an exact output to copy blindly.

## Objective
Upgrade the currently unfriendly post-login navigation and dashboard to a coherent, accessible government-branded experience. **Keep the existing user-supplied logo and palette**. Implement working Moodle navigation rather than static HTML previews. Do not change Moodle core.

## Critical UX architecture
1. Distinguish Site home, the authenticated Dashboard (`/my/`) and My Courses (`/my/courses.php`, if this route applies to installed Moodle). Redirect only if appropriate to current Moodle settings and preserve admin access.
2. Compact global header: colour stripe, intact logo, concise portal name, single search entry point that leads to actual Moodle search/courses, user menu and supported notification controls. Do not duplicate navigation levels.
3. Persistent left sidebar on desktop (approx. 248px) with meaningful icons, active state, logical groups, collapse/expand support and keyboard navigation. Use the 40+ supplied `assets/icons/*.svg`, coloured by CSS currentColor.
4. Primary: Dashboard, My Courses, Course Catalogue, Calendar, Announcements, Certificates where supported, Reports where role-appropriate.
5. Separate collapsible Administration group: Users, Courses, Enrolments, Grades, Cohorts & Campuses, Competencies, Attendance, Site Administration. Show each action **only** if its Moodle capability is permitted and there is a working destination; do not show administrative links to students. Contextual course actions belong on course pages, not duplicated in the global sidebar.
6. Bottom sidebar: Help & Support. On mobile switch to an accessible focus-managed drawer, not an overflowing horizontal menu.
7. Restore proper full-width dashboard container; eliminate the large blank left side and off-centre grey default course tree. Preserve keyboard-accessible native course-category browsing in the Catalogue.
8. Personalised content: role-sensitive welcome, relevant stats, course cards, upcoming activities, announcements and permitted quick actions. Stats, progress and dates must come from Moodle data; no mock numbers from screenshot. If data is absent, use honest empty states.
9. Course cards: six provided WebP thumbnails as optional sample defaults for the six demo courses; fall back to course image or `course-placeholder.svg`; accessible links; actual course names, visible category and relevant progress. 3/2/1 columns responsive.
10. Use small meaningful icons across sidebar, search, card metadata, actions, metrics, deadlines, announcements, course activities and empty states. Maintain consistent size/stroke. Do not substitute icon-only buttons where text is required for clarity.

## Visual rules
- Load the exact provided `assets/branding/eastern-cape-health-logo.png`.
- Use token definitions in `styles/tokens.css`. Primary black/white with green `#087C4A`; gold `#E5B522`, red `#D73527`, blue `#20458F` as restrained government accents. Screenshot/logo override approximate token values.
- Top colour strip: `assets/graphics/government-stripe.svg` or CSS equivalent.
- Light background, white cards, crisp black text, 8–16px rounded corners, subtle borders. No pink/purple or arbitrary gradients. Accessible contrast, visible focus indicators, responsive typography.
- All paths and site title must be Moodle-configurable. Do not use fake government authorisation badges.

## Engineering process
1. Inventory current implementations and assets. Check actual routes, Moodle theme architecture and plugin availability. State planned files and preservation strategy.
2. Implement in the supported child/custom theme using Mustache/SCSS/renderers/hooks for the detected Moodle version; retain existing block/navigation compatibility. Do not modify core.
3. Integrate icon SVGs as safe theme assets (not untrusted inline markup), supply aria-hidden for decorative icons, accessible names for icon-only actions. Account for SVG injection/content security concerns.
4. Use existing real Moodle data services and capability checks; ensure efficient fetching/no N+1 dashboard queries.
5. Verify dashboard/admin/teacher/student navigation and no duplicate breadcrumbs; preserve Moodle edit mode and relevant settings.
6. Purge theme caches, compile theme assets with appropriate version-specific methods, run lint/tests and manual breakpoint testing.
7. Take screenshots at 1440, 1024, 768 and 390px, plus student/lecturer/admin views. Compare to supplied visual reference and fix regressions.
8. Update TODO.md with completed subtasks, remaining blockers and file list.

## Acceptance criteria
- Site logo and palette intact; navigation clean and legible.
- Exactly one obvious primary nav per context; active item accurately reflects route.
- Role-specific administrative links only appear for authorised users.
- Course cards open real courses, progress and stats are accurate.
- All icons load and meet accessible label requirements.
- No empty left column, horizontal overflow, duplicate titles or nonworking click targets.
- Mobile drawer works with keyboard, escape, backdrop, focus trap and restore.
- Existing Moodle built-in pages, editing and administrative tools remain functional.

Implement now, not only document proposals. If any feature is blocked, finish independent work and report exact blockers.
