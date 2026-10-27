# Moodle

<p align="center"><a href="https://moodle.org" target="_blank" title="Moodle Website">
  <img src="https://raw.githubusercontent.com/moodle/moodle/main/.github/moodlelogo.svg" alt="The Moodle Logo">
</a></p>

[Moodle][1] is the World's Open Source Learning Platform, widely used around the world by countless universities, schools, companies, and all manner of organisations and individuals.

Moodle is designed to allow educators, administrators and learners to create personalised learning environments with a single robust, secure and integrated system.

## Documentation

- Read our [User documentation][3]
- Discover our [developer documentation][5]
- Take a look at our [demo site][4]

## Community

[moodle.org][1] is the central hub for the Moodle Community, with spaces for educators, administrators and developers to meet and work together.

You may also be interested in:

- attending a [Moodle Moot][6]
- our regular series of [developer meetings][7]
- the [Moodle User Association][8]

## Installation and hosting

Moodle is Free, and Open Source software. You can easily [download Moodle][9] and run it on your own web server, however you may prefer to work with one of our experienced [Moodle Partners][10].

Moodle also offers hosting through both [MoodleCloud][11], and our [partner network][10].

## License

Moodle is provided freely as open source software, under version 3 of the GNU General Public License. See our [license page][12] for more information.

[1]: https://moodle.org
[2]: https://moodle.com
[3]: https://docs.moodle.org/
[4]: https://sandbox.moodledemo.net/
[5]: https://moodledev.io
[6]: https://moodle.com/events/mootglobal/
[7]: https://moodledev.io/general/community/meetings
[8]: https://moodleassociation.org/
[9]: https://download.moodle.org
[10]: https://moodle.com/partners
[11]: https://moodle.com/cloud
[12]: https://moodledev.io/general/license

## Docker deployment on Ubuntu

This checkout includes a Docker Compose setup for the Moodle 5.3 source tree. It runs Apache/PHP and MariaDB in separate containers. Keep the `.env` file and database backups private.

### 1. Prepare Ubuntu

Install Docker Engine and the Docker Compose plugin from Docker's official Ubuntu instructions. Then clone this repository (use a GitHub deploy key or other read-only credential if it is private):

```sh
git clone https://github.com/amirsaleh-salehzadeh/moodle.git
cd moodle
cp .env.example .env
```

Edit `.env`. Set strong, different passwords for `MOODLE_DB_PASSWORD` and `MARIADB_ROOT_PASSWORD`, and set `MOODLE_WWWROOT` to the final URL, such as `https://learn.example.org`. Set `MOODLE_DB_NAME` to `moodle_local_20261026` when restoring the supplied local database dump. Do not commit `.env`.

### 2. Copy and restore the database

Transfer the SQL dump from `backups/` to the server with a secure file transfer method, for example `scp`. Start MariaDB first, then import the dump:

```sh
docker compose up -d db
docker compose exec -T db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"' < backups/moodle-db-YYYYMMDD-HHMMSS.sql
```

Replace the example filename with the transferred dump's actual name. The import creates the database named inside the dump, so `MOODLE_DB_NAME` must match it. Back up and transfer the Moodle data directory separately if you need uploaded course files; a SQL dump alone does not contain those files.

### 3. Start Moodle

```sh
docker compose up -d --build
docker compose logs -f moodle
```

The site listens on port 8080 by default. Put a TLS reverse proxy such as Caddy or Nginx in front for a public deployment, expose only the proxy's HTTPS port, and configure `.env` with `MOODLE_REVERSE_PROXY=true` and `MOODLE_SSL_PROXY=true` when the proxy terminates HTTPS. Set `MOODLE_WWWROOT` to the public HTTPS URL before first boot.

The imported database may contain the old `localhost:8080` URL. After the site starts, run Moodle's URL replacement command (update the old URL and domain to match your actual database and site URL):

```sh
docker compose exec moodle php admin/cli/replace.php --search='http://localhost:8080' --replace='https://learn.example.org'
docker compose exec moodle php admin/cli/purge_caches.php
```

Keep `dbdata` and `moodledata` volumes, `.env`, and off-server backups in your backup plan. For updates, pull the intended Git revision and rebuild with `docker compose up -d --build`; take database and Moodle data backups first.
