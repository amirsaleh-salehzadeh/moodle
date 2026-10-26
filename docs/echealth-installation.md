# Eastern Cape Health Moodle local setup

## Local app

This checkout's Moodle site URL is `http://localhost:8080`; its public document root is `public/`. XAMPP's PHP 8.5.12 CLI is at `C:\xampp8.5\php\php.exe`.

From PowerShell in the Moodle root, start the local app with:

```powershell
& 'C:\xampp8.5\php\php.exe' -S localhost:8080 -t public
```

Open `http://localhost:8080`. Apache may also serve the project if its virtual host points to this checkout's `public/` directory and uses the same PHP 8.5 runtime; this Moodle config is already set to port 8080.

Moodle 5.3 requires PHP 8.3 or newer and `sodium`. Moodle currently marks PHP 8.5 support as in development, so use this XAMPP setup for local demonstration only. Do not point the demo seeder at a production database.

## Theme

The `theme_echealth` Boost child theme is installed and active. Its source is `public/theme/echealth`; the supplied department emblem is included unchanged at `public/theme/echealth/pix/ec-health-logo.png`.

After theme changes, stop the local PHP server, purge Moodle caches and restart it:

```powershell
& 'C:\xampp8.5\php\php.exe' admin/cli/purge_caches.php
& 'C:\xampp8.5\php\php.exe' -S localhost:8080 -t public
```

## Demo data

The isolated local Moodle database is already populated. The one-time seed scripts are:

- `scripts/echealth_seed_demo.php` — creates six campus categories and cohorts, 36 campus course offerings, 24 learners, six educators, course sections and initial campus enrolments.
- `scripts/echealth_complete_demo.php` — adds the remaining campus learners, two training administrators and one training manager, and configures the front page.
- `scripts/echealth_add_learning_pages.php` — adds one core Moodle learning guide page to each course.
- `scripts/echealth_add_assessments.php` — adds one core Moodle Assignment to each course.

Run these only on a fresh copy of this local demo environment. They deliberately refuse to operate against other database names or hosts, and the scripts are one-time operations. Generated demo credentials are stored outside the web root in Moodle's local data directory. Reset or remove demo records by restoring a database and data-directory backup; do not run broad deletion queries against Moodle.

All names and content are fictional, use reserved `.test` email addresses and contain no real student or patient data. Learning content is illustrative and is not approved clinical guidance.

## Current limitations

No quizzes or attempts, sample grades, assignment submissions, sample completion records, certificates, attendance, announcements or reporting views have been created. The courses include functional Assignment activities; actual submission and marking workflows still need educator/learner validation. Production use also requires a supported runtime, HTTPS, backups, institutional security controls and independent accessibility and clinical governance review.
