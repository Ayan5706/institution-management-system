# Institution Management System (IMS)

<p align="center">
	<img src="public/assets/images/logo.svg" alt="IMS logo" width="120">
</p>

<p align="center">
	A PHP-based institution management platform for students, teachers, attendance, fees, subjects, timetables, reports, and role-based access.
</p>

<p align="center">
	<a href="#overview">Overview</a> ·
	<a href="#features">Features</a> ·
	<a href="#screenshots">Screenshots</a> ·
	<a href="#roles">Roles</a> ·
	<a href="#setup">Setup</a>
</p>

## Overview

<details open>
<summary>What this project does</summary>

This application is built as a lightweight MVC-style system for local PHP, Apache, and MySQL deployments such as XAMPP. It keeps the common institution workflows in one place, with controllers, models, views, middleware, helpers, and services organized for day-to-day school administration.

</details>

## Features

<table>
	<tr>
		<td><strong>Student Management</strong><br>Profiles, enrollment details, and academic records.</td>
		<td><strong>Attendance Tracking</strong><br>Fast attendance capture and reporting.</td>
		<td><strong>Fee Management</strong><br>Payments, balances, and semester fee handling.</td>
	</tr>
	<tr>
		<td><strong>Class Scheduling</strong><br>Timetables and structured schedules.</td>
		<td><strong>Teacher Management</strong><br>Assignments, workload, and subject mapping.</td>
		<td><strong>Reports & Analytics</strong><br>Operational summaries and performance insights.</td>
	</tr>
</table>

<details>
<summary>More included workflows</summary>

- Role-based authentication and dashboards
- Email activation and mail workflows
- Upload and storage management
- Authentication middleware and protection layers

</details>

## Tech Stack

<details>
<summary>Core technologies</summary>

- PHP 8+
- MySQL
- PHPMailer
- Firebase JWT
- Apache/XAMPP for local development

</details>

## Project Structure

<details>
<summary>Repository layout</summary>

- `app/` application controllers, models, services, views, middleware, and config
- `public/` public entry point and static assets
- `routes/` route definitions
- `database/` SQL schema and migrations
- `storage/` cache, logs, exports, and temporary files

</details>

## Screenshots

<details open>
<summary>Visual previews</summary>

These are project assets already included in the repository. If you want real runtime screenshots later, I can add them after you provide captures from the running app.

| Preview | Image |
| --- | --- |
| Homepage | ![Homepage preview](public/assets/images/illustrations/Homepage_cover.png) |
| Branding | ![IMS logo](public/assets/images/logo.svg) |
| Students | ![Students](public/assets/images/students.png) |

</details>

## Roles

<details>
<summary>Role-based modules</summary>

| Role | Focus |
| --- | --- |
| Admin | System setup, user control, and global management |
| Principal | Oversight, approvals, and academic visibility |
| Teacher | Class, attendance, and student-related workflows |
| Student | Profile access, fees, and academic records |
| Management | Reporting and administrative monitoring |

</details>

## Setup

<details>
<summary>How to run locally</summary>

1. Clone the repository.
2. Import the database from `database/ims_final_db.sql`.
3. Configure environment settings in `app/Config/env.php` and mail/database settings in `app/Config/`.
4. Run the project through Apache/XAMPP with the document root pointing to `public/`.
5. Open the app in your browser at `http://localhost/`.

</details>

## Notes

- Some local files such as `.env` are not meant to be committed if they contain secrets.
- The screenshot section currently uses repository assets, not captured browser shots.

