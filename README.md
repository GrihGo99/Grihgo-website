# GrihGO Website

PHP website for GrihGO Digital & Tech, including the public site, client portal, manager console, and admin console.

## Requirements

- PHP 8.0 or later
- MySQL/MariaDB with PDO MySQL, or PDO SQLite for local fallback
- A web server configured to serve this directory and process PHP

## Setup

1. Use PHP 8.0+ with PDO MySQL enabled. In production, configure `APP_ENV=production`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`.
2. On cPanel, credentials can be kept outside the web root in `/home/<account>/grihgo-config.php`. That file must return an array with those keys plus `INITIAL_ADMIN_EMAIL` and a unique `INITIAL_ADMIN_PASSWORD` of at least 16 characters. The app creates the first admin after the schema is imported and a database connection succeeds.
3. Create a new, empty cPanel database and select it in phpMyAdmin. Import, in order: `database/grihgo_database.sql`, `database/part3_client_portal.sql`, `database/migrations/part4_admin_panel.sql`, then `database/migrations/production_fix_001.sql`. The master schema intentionally drops/recreates its tables, so only import it into a new empty database.
4. Point the web server document root at this directory. Keep `config/`, `database/`, `logs/`, and uploaded files protected from direct access. Do not upload local SQLite data or logs.

Local SQLite data, logs, uploads, scratch scripts, and environment files are excluded from Git.
