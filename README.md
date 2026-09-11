<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<h1 align="center">MediCare Hospital</h1>

<p align="center">A modern hospital appointment platform for patients, doctors, and administrators.</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3 or newer">
  <img src="https://img.shields.io/badge/Frontend-Vite%20%2B%20Bootstrap-7952B3?style=flat-square&logo=vite&logoColor=white" alt="Vite and Bootstrap">
</p>

## Overview

MediCare brings the core hospital experience into one focused web application. Visitors can explore departments and doctors, patients can book and track appointments, doctors can manage their availability and consultations, and administrators can manage the clinical directory and day-to-day operations.

## What is included

### Public experience

- Hospital home, about, services, department, doctor, and contact pages
- Clinical directory built around departments and doctor profiles
- Contact form for general enquiries

### Patient portal

- Patient registration and role-based authentication
- Appointment booking from doctor availability
- Appointment history, details, and cancellation
- Profile management and secure messages

### Doctor portal

- Dashboard with upcoming clinical activity
- Appointment review and status updates
- Patient list and patient detail views
- Weekly schedule creation, editing, and removal
- Doctor profile management

### Administration

- Dashboard metrics for patients, doctors, departments, and appointments
- Department and doctor directory management
- Appointment oversight and contact message management
- Protected role-based access for operational workflows

## Tech stack

- **Backend:** Laravel 13, PHP 8.3+
- **Database:** Laravel migrations, Eloquent ORM, and seeders
- **Frontend:** Blade, Bootstrap 5, Tailwind CSS 4, Vite
- **Testing:** PHPUnit
- **Code quality:** Laravel Pint

## Quick start

### Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- A supported Laravel database such as SQLite or MySQL

### Installation

```bash
git clone <repository-url>
cd Medicare-hospital
composer install
copy .env.example .env # Windows
# cp .env.example .env # macOS/Linux
php artisan key:generate
```

Configure the database values in `.env`, then run the migrations and demo seeder:

```bash
php artisan migrate:fresh --seed
npm install
npm run build
```

Start the application:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

For an active development workflow with Vite hot reload, use:

```bash
composer run dev
```

## Demo accounts

`php artisan migrate:fresh --seed` creates stable demo records for local development. These credentials are not intended for production use.

| Role    | Email                         | Password        |
| ------- | ----------------------------- | --------------- |
| Admin   | `admin@medicare.test`         | `Admin@12345`   |
| Doctor  | `doctor.carter@medicare.test` | `Doctor@12345`  |
| Patient | `patient1@medicare.test`      | `Patient@12345` |

Additional seeded doctors and patients are available for testing appointment workflows.

## Useful commands

```bash
# Reset the local database and restore demo data
php artisan migrate:fresh --seed

# Run the test suite
php artisan test --compact

# Format changed PHP files
vendor/bin/pint --dirty --format agent

# Build production frontend assets
npm run build
```

## Project structure

```text
app/                 Application services, models, policies, and controllers
database/            Migrations, factories, and demo seeders
resources/views/     Blade pages and role-based portal views
resources/css/       Application styles
resources/js/        Frontend entrypoint
routes/              Public, patient, doctor, and admin routes
tests/               Feature and unit tests
```

## Security notes

- Demo accounts and seeded personal data are for local development only.
- Keep `.env` out of version control and use strong production credentials.
- Review authentication, authorization, validation, and database settings before deploying.

## License

This project is built with the [Laravel framework](https://laravel.com), which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
