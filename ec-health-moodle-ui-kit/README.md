# Eastern Cape Department of Health — Moodle navigation UI asset kit

**Contents:** user-provided departmental logo; 40+ individual, minified SVG outline icons; reusable SVG graphics; six lightweight WebP/JPEG course-thumbnail demo crops; CSS colour tokens/components; implementation and QA Codex prompts; full dashboard visual reference.

## Usage
1. Open `preview/dashboard-visual-reference.png`.
2. Give the complete ZIP to Codex, together with the original repository and the user-provided screenshots.
3. Run `prompts/01_IMPLEMENT_NAVIGATION.md`, then `prompts/02_QA_AND_ACCESSIBILITY.md`.
4. Build theme assets with the Moodle-version-approved pipeline, purge caches and verify all roles.

## Important
- The departmental logo is copied verbatim. Do not recolour or redraw.
- The government strip is a design motif, not an assertion of official approval.
- The small sample course pictures are generated-concept crops, not official photographs; obtain usage-approved, high-resolution photographs for production.
- The design reference image contains **illustrative** statistics, dates and campus names. Never hardcode them.
- Don’t automatically display site-administration controls to learners. Moodle's built-in role/capability checks must determine what is shown.
- Do not directly patch Moodle core, database schemas, or hardcode URLs.
