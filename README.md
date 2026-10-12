# GrihGO Website

PHP website for GrihGO Digital & Tech, including the public site, client portal, manager console, and admin console.

## Requirements

- PHP 8.0 or later
- MySQL/MariaDB with PDO MySQL, or PDO SQLite for local fallback
- A web server configured to serve this directory and process PHP

## Setup

1. Configure the database connection with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables.
2. For a fresh database, set `INITIAL_ADMIN_EMAIL` and a unique `INITIAL_ADMIN_PASSWORD` of at least 16 characters before the schema is initialized. No demo administrator or client accounts are created.
3. Point the web server document root at this directory. Keep `config/`, `database/`, `logs/`, and uploaded files protected from direct access.

Local SQLite data, logs, uploads, scratch scripts, and environment files are excluded from Git.
