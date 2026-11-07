# Prerequisites & Setup — Job Portal / Recruitment System (Windows)

Everything below is Windows-specific. This project is plain PHP (custom MVC) with Composer dependencies for email and Excel import.

## 1. Install XAMPP

1. Download XAMPP with **PHP 8.1 or newer** from https://www.apachefriends.org and run the installer (default path `C:\xampp`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Add `C:\xampp\php` and `C:\xampp\mysql\bin` to your system `Path` (System Properties → Environment Variables), then open a new terminal and confirm `php -v` / `mysql --version`.

## 2. Install Composer

Download and run `Composer-Setup.exe` from https://getcomposer.org/download/, then confirm `composer -V` in a new terminal.

## 3. Enable required PHP extensions

Needed for `phpmailer/phpmailer` and `phpoffice/phpspreadsheet`:

1. Edit `C:\xampp\php\php.ini`.
2. Uncomment:
   ```
   extension=gd
   extension=zip
   ```
3. Save and restart Apache from the XAMPP Control Panel.

## 4. Configure MySQL/MariaDB port

`config/database.php` is committed with `host=127.0.0.1, port=3306` — XAMPP's own default MariaDB port, so no change should be needed on a standard XAMPP install. Only edit `config/database.php` if your local MySQL runs on a different port.

## 5. Install project dependencies

```
composer install
```

## 6. Database setup

```
mysql -u root -h 127.0.0.1 -P 3306 < database/schema.sql
```
(adjust `-P` to your port). This creates the `job_portal_recruitment_system` database, tables, and seed data for users/profiles/skills/jobs — but **not** applications, which is a separate step below.

## 7. Generate seeded applications + match scores

Run once (re-runnable any time — it truncates and regenerates both tables):
```
php database/generate_applications.php
```
This computes each seeded application's match score with the real `Core\MatchScoreCalculator` against the seeded data, so demo numbers are genuine engine output.

## 8. Configure outgoing email

Edit `config/mail.php` with SMTP credentials from a free test inbox such as https://mailtrap.io, so approval/status-change/reset emails can be sent and inspected locally. Without this, `Core\Mailer::send()` fails silently (logged, not thrown) — the app still works for a demo.

## 9. Verify everything

```
php -v
composer -V
mysql -u root -h 127.0.0.1 -P 3306 -e "SELECT 1;"
```
Ready — see `HOW-TO-RUN.md` to start the app.
