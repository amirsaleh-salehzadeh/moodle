# Moodle 5.3+ plugin compatibility audit

**Reviewed:** 27 October 2026  
**Environment:** Moodle 5.3+ (branch 503, build 20261021); local MariaDB 13.0.2; theme `echealth` (Boost child).  
**Source of truth:** local plugin component records and code paths, plus the official Moodle Marketplace release metadata linked below.

## Status key

- **Installed; configuration/workflow unverified** — component code and Moodle version record exist, but an end-to-end activity workflow has not been proven.
- **Compatible; not installed** — an official stable 5.3 release is available, but the package was not downloaded or installed during this audit.
- **Blocked for this Moodle version** — no official 5.3-compatible release was verified, or the official listing stops before 5.3.
- **Core substitute** — use Moodle core where it covers the stated requirement.

## Installed plugins and core substitutes

| Requirement | Component | Local evidence | Compatibility | Decision / verification |
|---|---|---|---|---|
| Attendance sessions/registers | `mod_attendance` | Installed version `2026090300`; source declares Moodle 5.3 / branch 503; local database component record matches | Declares branch 503 support | **Installed; configuration/workflow unverified.** No Attendance activity/session exists. Do not claim attendance recording or reporting works until a permitted lecturer/learner workflow is exercised. |
| Downloadable certificates | `mod_customcert` and certificate elements | Installed version `2026100503`; Moodle 5.3 requirement; installed element component records present | Declares Moodle 5.3 support | **Installed; configuration/workflow unverified.** No Custom Certificate activity/template/issued certificate exists. Department branding and eligibility criteria require an approved template/criteria before setup. |
| Progress indicators | Core Activity Completion and Course Completion, dashboard APIs | `completion` and course-completion APIs exist; theme dashboard uses Moodle completion APIs; zero course completion records | Core 5.3 | **Core substitute present; learner completion unverified.** Configure per-course activity criteria and course criteria; no progress claim should imply completions in the current data. |
| Institutional reports | Core Custom Reports / Report Builder | `public/reportbuilder/index.php` and Report Builder classes present | Core 5.3 | **Core feature present; reports unconfigured/unverified.** Define data-minimized reports and role access; current theme link is limited to site configuration capability. |
| Surveys/evaluation | Core Feedback activity `mod_feedback` | Code and installed version `2026100500` present; zero Feedback instances | Core 5.3 | **Core substitute present; unconfigured.** Create an approved evaluation activity and test response privacy/visibility before calling this workflow available. |
| Advanced SQL reporting | Report Builder | Core component available; no accepted unmet SQL reporting specification supplied | Core 5.3 | **Use core.** Do not add SQL execution access unless a specific requirement remains unmet and a security review approves the design. |

## Optional plugin candidates

| Plugin | Component | Official version evidence | 5.3 compatibility | Decision |
|---|---|---|---|---|
| Checklist | `mod_checklist` | [Marketplace version 4.1.0.9](https://marketplace.moodle.com/plugins/231/versions?show=all), build `2026092500`, stable, automated prechecks passed; upstream repo is [davosmith/moodle-checklist](https://github.com/davosmith/moodle-checklist) | Explicitly lists 5.3 | **Compatible; not installed.** Optional activity for learner task checklists; core completion may already meet progress needs. Install only if the activity workflow is approved. |
| Teacher Checklist | `block_teacher_checklist` | [Marketplace version listing](https://marketplace.moodle.com/plugins/3734/versions) identifies v1.3.3, build `2026101900`, stable; maintainer Jean Lúcio | Explicitly lists 4.5, 5.0–5.3; no mandatory dependencies | **Compatible; not installed.** Optional teacher task overview block; integration and permissions remain unverified. |
| Flexible Sections | `format_flexsections` | [Marketplace version 5.0.5](https://marketplace.moodle.com/plugins/499/versions?show=all), build `2026100100`, stable; upstream [v5.0.5 source](https://github.com/marinaglancy/moodle-format_flexsections/tree/v5.0.5) | Explicitly lists 5.0–5.3 and fixes noted for Moodle 5.3 | **Compatible; not installed.** Candidate for nested sections. Do not switch existing courses until a copy-based course-format QA is approved. |
| Soft Course Format | `format_softcourse` | [Marketplace listing](https://marketplace.moodle.com/plugins/format_softcourse) reports support through 5.2; upstream maintainer is Pimenko | No 5.3 release verified | **Blocked for 5.3.** Component name is `format_softcourse`; do not install. |
| Tiles Course Format | `format_tiles` | Current community discussion reports the format is no longer maintained; no official 5.3 release was verified | Not verified | **Blocked.** Do not install or convert existing courses. |
| Completion Progress | `block_completion_progress` | Official Marketplace metadata reviewed; no stable release explicitly listing 5.3 was verified | No 5.3 release verified | **Blocked pending compatible release.** Use core Activity Completion, Course Completion and dashboard components. |
| Configurable Reports | `block_configurable_reports` | Marketplace listing and support forum reviewed; no current 5.3 release declaration verified | Not verified | **Blocked pending release evidence.** Prefer core Report Builder. |
| Questionnaire | `mod_questionnaire` | Current official listing does not establish a 5.3-compatible release | Not verified | **Blocked pending release evidence.** Use core Feedback for evaluation. |
| Ad-hoc Database Queries | `report_customsql` | Official listing's supported-version history does not reach 5.3 | Not verified | **Blocked for 5.3.** Use Report Builder; restrict any future SQL execution feature to a reviewed trusted-administrator boundary. |

## Installation and backup record

- Before a plugin/database change, a development backup was created at `backups/dev-pre-plugin-20261027/`.
- The backup contains a full application copy, `moodledata`, and `moodle_local_20261026.sql`. The application `public/` tree was reconciled to source at 67,730 files / 464,053,054 bytes; `moodledata` contains 841 files / 14,937,386 bytes; the SQL dump is 2,595,626 bytes. See `BACKUP_INFO.md` inside the backup directory.
- No candidate plugin files were installed and no Moodle database upgrade was run. The official marketplace download could not be completed because the browser download authority gate denied access; direct shell download also failed its TLS authentication. No attempt was made to bypass these controls.
- Attendance and certificate configuration is held pending approved session rules, certificate template/eligibility, and available teaching-account verification. No sample activity or certificate was invented.

## Maintenance and security notes

- Recheck Marketplace version metadata and source release tags at the time of a future deployment; compatibility can change after this audit date.
- Keep third-party code on stable tags, review source and dependencies, and maintain a rollback plan. Do not deploy plugins to production from this development checkout.
- `mod_checklist`, `block_teacher_checklist` and `format_flexsections` have explicit stable Moodle 5.3 release metadata. This is compatibility evidence, not functional acceptance.
