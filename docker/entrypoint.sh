#!/bin/sh
set -eu

if [ ! -f /var/www/html/config.php ]; then
    : "${MOODLE_DB_HOST:?MOODLE_DB_HOST is required}"
    : "${MOODLE_DB_NAME:?MOODLE_DB_NAME is required}"
    : "${MOODLE_DB_USER:?MOODLE_DB_USER is required}"
    : "${MOODLE_DB_PASSWORD:?MOODLE_DB_PASSWORD is required}"
    : "${MOODLE_WWWROOT:?MOODLE_WWWROOT is required}"
    cat > /var/www/html/config.php <<'PHP'
<?php
unset($CFG);
global $CFG;
$CFG = new stdClass();
$CFG->dbtype = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost = getenv('MOODLE_DB_HOST');
$CFG->dbname = getenv('MOODLE_DB_NAME');
$CFG->dbuser = getenv('MOODLE_DB_USER');
$CFG->dbpass = getenv('MOODLE_DB_PASSWORD');
$CFG->prefix = getenv('MOODLE_DB_PREFIX') ?: 'mdl_';
$CFG->dboptions = ['dbpersist' => 0, 'dbport' => (int)(getenv('MOODLE_DB_PORT') ?: 3306), 'dbsocket' => '', 'dbcollation' => 'utf8mb4_unicode_ci'];
$CFG->wwwroot = getenv('MOODLE_WWWROOT');
$CFG->dataroot = '/var/www/moodledata';
$CFG->admin = 'admin';
$CFG->directorypermissions = 02777;
if (getenv('MOODLE_REVERSE_PROXY') === 'true') { $CFG->reverseproxy = true; }
if (getenv('MOODLE_SSL_PROXY') === 'true') { $CFG->sslproxy = true; }
require_once(__DIR__ . '/lib/setup.php');
PHP
    chown www-data:www-data /var/www/html/config.php
    chmod 0640 /var/www/html/config.php
fi

exec "$@"
