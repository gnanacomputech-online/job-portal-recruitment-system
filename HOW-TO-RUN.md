# How to Run — Job Portal / Recruitment System

Assumes prerequisites in `PREREQUISITES.md` are already done.

## One-time setup

```
composer install
mysql -u root -h 127.0.0.1 -P 3306 < database/schema.sql
php database/generate_applications.php
```

The third command generates seeded applications and real match scores — required, since the schema import alone doesn't create them.

To wipe the seeded data and start over, drop the database first, then re-run all three commands above (re-running the import directly without dropping first fails with "table already exists"):
```
mysql -u root -h 127.0.0.1 -P 3306 -e "DROP DATABASE IF EXISTS job_portal_recruitment_system;"
```

Configure `config/mail.php` with SMTP credentials (e.g. Mailtrap) so approval/status/reset emails can be sent (optional for a basic demo — sending fails silently without it).

## Start MySQL

Open **XAMPP Control Panel** and make sure **MySQL** is running.

## Run the app

From this project's folder:
```
php -S localhost:8001 -t public public/index.php
```
(`-t public` is required for static assets to load.)

## Open it

Visit http://localhost:8001/login and sign in with any of the demo credentials in `README.md` under "Default Login Credentials".

## Stop the app

`Ctrl+C` in the terminal running the `php -S` command.
