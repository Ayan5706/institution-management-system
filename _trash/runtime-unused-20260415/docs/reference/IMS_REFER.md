# Institution Management System (IMS)
### Complete Technical Reference — PHP + MySQL Edition

> This document is the single authoritative source of truth for the PHP + MySQL implementation of IMS. Every table, field, MySQL-specific constraint, trigger, PHP pattern, API endpoint, and UI screen is specified here. An AI agent must implement exactly what is written.

---

## Project Summary

| Property | Value |
|---|---|
| **System Name** | Institution Management System (IMS) |
| **Backend** | PHP 8.2+ — Pure native PHP, no framework. PDO for all DB operations. |
| **Database** | MySQL 8.0+ — InnoDB engine, utf8mb4 charset, strict mode, triggers. |
| **Auth** | JWT via `firebase/php-jwt` (composer). Access token: 60 min. Refresh: 7 days. |
| **Password Hashing** | `password_hash(PASSWORD_BCRYPT)` / `password_verify()` |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript, Bootstrap 5.3. Native PHP templates. |
| **Routing** | Custom PHP router in `public/index.php`. All API routes under `/api/`. |
| **Background Task** | PHP CLI script: `cron/expire_reset_requests.php`. Run daily via crontab. |
| **Tables** | 13 tables + jwt_blacklist = **14 total** MySQL tables. |
| **Roles** | 6 roles: `PRINCIPAL`, `VP`, `MANAGER`, `ACCOUNTANT`, `TEACHER`, `STUDENT` |
| **UI Style** | Clean & minimal — white backgrounds, light grays, subtle borders. |

---

## Table of Contents

- [Part 1 — System Overview](#part-1--system-overview)
- [Part 2 — Roles and Authority Chain](#part-2--roles-and-authority-chain)
- [Part 3 — Database Schema (14 MySQL Tables)](#part-3--database-schema-14-mysql-tables)
- [Part 4 — PHP Implementation Rules](#part-4--php-implementation-rules)
- [Part 5 — API Endpoints](#part-5--api-endpoints)
- [Part 6 — Frontend UI Specification](#part-6--frontend-ui-specification)
- [Part 7 — Complete Business Rules](#part-7--complete-business-rules)
- [Part 8 — Identified Loopholes and Issues](#part-8--identified-loopholes-and-issues)
- [Part 9 — Project Folder Structure](#part-9--project-folder-structure)
- [Part 10 — AI Agent Implementation Prompt](#part-10--ai-agent-implementation-prompt)

---

## PART 1 — System Overview

### 1.1 Purpose

The Institution Management System (IMS) is a role-based web application for managing the academic and administrative operations of a degree-level institution. It manages multiple academic programs (e.g., BCA, MSc, MCA), semesters, subjects, teachers, students, timetables, attendance, and fee payments. Every operation is role-gated — users see and do only what their role permits.

### 1.2 Technology Stack

| Layer | Technology & Detail |
|---|---|
| **Backend** | PHP 8.2+. No framework. Custom router in `public/index.php`. Controllers in `app/controllers/`. Services in `app/services/`. All DB via PDO with prepared statements only. |
| **Database** | MySQL 8.0+. InnoDB engine on every table. utf8mb4 charset. `STRICT_TRANS_TABLES` mode. Triggers for `is_current` enforcement. |
| **JWT Auth** | `firebase/php-jwt` (composer). JWT includes: `user_id`, `role`, `jti` (unique token ID), `iat`, `exp`. Access token: 3600s. Refresh token: 604800s (7 days). |
| **Token Blacklist** | Custom `jwt_blacklist` MySQL table. Stores `jti` on logout or deactivation. Checked on every protected request. |
| **Passwords** | `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])`. Verified with `password_verify()`. |
| **Frontend** | HTML5 + CSS3 + Vanilla JavaScript + Bootstrap 5.3. Native PHP templates (no Twig, no Blade). PHP `include`/`require` for partials. |
| **Routing** | `public/index.php` is the single entry point. API routes return JSON. Web routes return PHP-rendered HTML. |
| **Background Task** | `cron/expire_reset_requests.php` — PHP CLI script. Crontab: `0 0 * * * php /path/to/cron/expire_reset_requests.php` |
| **Composer Deps** | `firebase/php-jwt` only. All other functionality uses PHP 8.2 built-ins. |
| **Dev Tools** | VS Code, GitHub, Postman. |

### 1.3 Core Design Principles

- **Role-based access** — every user sees and acts on only what their role permits.
- **Program-driven hierarchy** — Programs → Semesters → Subjects → Teacher Assignments → Timetable → Attendance. Fees tracked per student per semester.
- **Single identity store** — all six roles share one `users` table. `role` is a MySQL ENUM column.
- **No computed values stored** — `term` (odd/even), `pending_amount`, `fee_status` are PHP-computed before JSON output. Never stored in DB.
- **Two-layer enforcement** — every constraint enforced in PHP (application) AND MySQL (DB constraints + triggers).
- **No public signup** — all accounts created top-down by the authorized role.
- **Soft deletes only** — accounts deactivated via `is_active=0`, never deleted.
- **Append-only audit trail** — every significant action written to `audit_log`. No `UPDATE` or `DELETE` on this table.
- **Parameterized queries only** — every SQL value is a PDO bound parameter. String interpolation in SQL is forbidden.

---

## PART 2 — Roles and Authority Chain

### 2.1 The Six Roles

| Role | ENUM Value | Core Responsibility | Login ID Type |
|---|---|---|---|
| Principal | `PRINCIPAL` | System supervision. Creates admin-tier accounts. | Custom ID set by system owner. |
| Vice Principal | `VP` | Academic control. Programs, semesters, teachers, subjects, timetable. | Custom VP ID assigned by Principal. |
| Manager | `MANAGER` | Student lifecycle. Enrollment, activation, CSV upload. | Custom manager ID by Principal. |
| Accountant | `ACCOUNTANT` | Financial control. Semester fees, student payments. | Custom accountant ID by Principal. |
| Teacher | `TEACHER` | Attendance marking for assigned sessions. | Staff ID assigned by VP. |
| Student | `STUDENT` | Personal data view only. | Registration number by Manager. |

### 2.2 Account Creation Chain

| Account Being Created | Created By | Method |
|---|---|---|
| Principal | System Owner | PHP CLI seed script: `php database/seeds/create_principal.php` |
| VP, Manager, Accountant | Principal | Principal dashboard → Account Management. |
| Teacher | Vice Principal | VP dashboard → Teacher Management. |
| Student | Manager | Manager dashboard → CSV Upload (primary) or Manual Add (fallback). |

### 2.3 Password Reset Approval Chain

| Requesting Role | Request Goes To | Mechanism |
|---|---|---|
| Student | Manager | Dashboard alert on Manager screen. |
| Teacher | Vice Principal | Dashboard alert on VP screen. |
| Manager / VP / Accountant | Principal | Dashboard alert on Principal screen. |
| Principal | System Owner | Out-of-system. Owner runs seed script or direct MySQL. |

> **RULE:** Requests expire after 7 days. PHP cron script sets `status=EXPIRED` daily via:
> ```sql
> UPDATE password_reset_requests SET status='EXPIRED', resolved_at=NOW()
> WHERE status='PENDING' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
> ```

> **RULE:** On approval: PHP generates a random 10-char alphanumeric temp password, hashes with `password_hash(PASSWORD_BCRYPT)`, stores in `users.password_hash`, sets `must_change_password=1`.

---

## PART 3 — Database Schema (14 MySQL Tables)

> All 14 tables use `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. Implement exactly as specified.

> **CRITICAL:**
> - `term` is **NOT stored**. PHP computes it: `($semester_number % 2 == 1) ? "Odd" : "Even"`
> - `pending_amount` is **NOT stored**. PHP computes: `$semester_fee_amount - $amount_paid`
> - `fee_status` is **NOT stored**. PHP computes: `($pending == 0) ? "Paid" : "Pending"`
> - All SQL values must be PDO bound parameters. No string interpolation in SQL ever.
> - `TINYINT(1)` is used for boolean fields. `1=true/active`, `0=false/inactive`. Always cast in PHP: `(bool)$row['is_active']` or compare strictly: `$row['is_active'] == 1`

---

### Table 1 — `users`

Stores every user of the system regardless of role. Single identity table.

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Auto-incrementing primary key. |
| `role` | `ENUM('PRINCIPAL','VP','MANAGER','ACCOUNTANT','TEACHER','STUDENT')` | `NOT NULL` | Role of this user. Immutable after INSERT. |
| `login_id` | `VARCHAR(50)` | `NOT NULL, UNIQUE` | Login identifier. Globally unique across all users. |
| `password_hash` | `VARCHAR(255)` | `NOT NULL` | PHP bcrypt hash. Never store plaintext. |
| `full_name` | `VARCHAR(150)` | `NOT NULL` | Full name of the user. |
| `email` | `VARCHAR(191)` | `NULL` | Optional contact email. |
| `phone` | `VARCHAR(20)` | `NULL` | Optional phone number. |
| `is_active` | `TINYINT(1)` | `NOT NULL DEFAULT 1` | `1=active`, `0=deactivated`. Checked on every protected request after JWT verification. |
| `must_change_password` | `TINYINT(1)` | `NOT NULL DEFAULT 1` | `1=must change before dashboard access`. Set on creation and after every reset approval. |
| `created_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | User who created this account. NULL for Principal. |
| `created_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | Auto-set on INSERT. |
| `updated_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Auto-updated on any change. |

---

### Table 2 — `student_profiles`

Student-specific fields. One-to-one with `users` where `role=STUDENT`.

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL UNIQUE, FK→users.id CASCADE` | One-to-one with users. CASCADE delete. |
| `registration_number` | `VARCHAR(30)` | `NOT NULL UNIQUE` | Student reg number. Also stored in `users.login_id`. Must match. |
| `date_of_birth` | `DATE` | `NOT NULL` | Student date of birth. |
| `program_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→programs.id RESTRICT` | Enrolled program. Set at creation. Read-only after INSERT. RESTRICT prevents program deletion if students exist. |

> **CRITICAL:** `program_id` is **READ-ONLY** after INSERT. No PHP endpoint may UPDATE this column. Changing program requires deactivating old account and creating a new one.

---

### Table 3 — `programs`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `program_name` | `VARCHAR(100)` | `NOT NULL UNIQUE` | Full name. e.g., "Bachelor of Computer Applications". |
| `program_code` | `VARCHAR(10)` | `NOT NULL UNIQUE` | Uppercase short code. e.g., "BCA". PHP validates regex `/^[A-Z]{2,10}$/`. `strtoupper()` enforced before INSERT. |
| `duration_semesters` | `TINYINT UNSIGNED` | `NOT NULL` | Total semesters in this program. min=1. PHP validates `>= 1`. |
| `is_active` | `TINYINT(1)` | `NOT NULL DEFAULT 1` | `1=active`. Cannot deactivate if active students or current semesters exist. |
| `created_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | VP who created the program. |
| `created_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | Timestamp of creation. |

---

### Table 4 — `semesters`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `program_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→programs.id CASCADE` | The program this semester belongs to. |
| `semester_number` | `TINYINT UNSIGNED` | `NOT NULL` | Position within program. PHP validates: `>= 1` AND `<= program.duration_semesters`. |
| `academic_year` | `VARCHAR(9)` | `NOT NULL` | Format: `YYYY-YYYY`. e.g., "2024-2025". PHP validates: second year == first year + 1. |
| `is_current` | `TINYINT(1)` | `NOT NULL DEFAULT 0` | `1=currently running semester for this program`. Enforced by MySQL BEFORE UPDATE TRIGGER + PHP pre-check. |
| `fee_amount` | `DECIMAL(10,2)` | `NULL DEFAULT NULL` | Set by Accountant. NULL = fee not yet configured. |

> **CRITICAL:** `UNIQUE KEY uq_semester (program_id, semester_number, academic_year)` — define in `CREATE TABLE`.

> **CRITICAL:** MySQL BEFORE UPDATE TRIGGER for `is_current` (replaces PostgreSQL partial unique index which MySQL does NOT support). When `is_current` is set to `1`, the trigger first sets `is_current=0` on all other semesters with the same `program_id`. PHP must ALSO clear others in a transaction before setting `is_current=1`, as a double-check.

> **COMPUTED:** `term` — PHP function: `($n % 2 == 1) ? "Odd" : "Even"`. Include in every semester API response as `"term"` field. Never stored.

---

### Table 5 — `subjects`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `semester_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→semesters.id CASCADE` | The semester this subject belongs to. |
| `subject_name` | `VARCHAR(150)` | `NOT NULL` | Full subject name. |
| `subject_code` | `VARCHAR(20)` | `NOT NULL` | Subject code. Unique within a semester. `UNIQUE KEY uq_subj_code (semester_id, subject_code)`. |

---

### Table 6 — `teacher_assignments`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `teacher_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→users.id CASCADE` | The teacher. PHP validates `users.role='TEACHER'`. |
| `subject_id` | `BIGINT UNSIGNED` | `NOT NULL UNIQUE, FK→subjects.id CASCADE` | UNIQUE: one teacher per subject. Each subject has exactly one teacher. |

> **RULE:** UNIQUE on `subject_id` enforced at DB level. Additionally PHP pre-INSERT check:
> ```sql
> SELECT ta.id FROM teacher_assignments ta
> JOIN subjects s ON ta.subject_id = s.id
> WHERE ta.teacher_id = ? AND s.semester_id = (SELECT semester_id FROM subjects WHERE id = ?)
> ```
> If result exists: return error `"Teacher already has an assignment in this semester."`

> **RULE:** `semester_id` is NOT stored. Derived via JOIN: `teacher_assignments JOIN subjects ON subject_id → subjects.semester_id`.

---

### Table 7 — `timetables`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `teacher_assignment_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→teacher_assignments.id CASCADE` | The teacher-subject assignment for this slot. |
| `day` | `ENUM('MON','TUE','WED','THU','FRI','SAT')` | `NOT NULL` | Day of the week. PHP validates against SystemConfig `WORKING_DAYS`. |
| `start_time` | `TIME` | `NOT NULL` | Class start. PHP validates `>= SystemConfig DAY_START_TIME`. |
| `end_time` | `TIME` | `NOT NULL` | Class end. PHP validates `<= DAY_END_TIME` and `> start_time`. |

> **RULE:** `UNIQUE KEY` on `(teacher_assignment_id, day, start_time)`.

> **RULE:** PHP pre-INSERT semester clash check:
> ```sql
> SELECT t.id FROM timetables t
> JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
> JOIN subjects s ON ta.subject_id = s.id
> WHERE s.semester_id = <current_semester_id>
>   AND t.day = ? AND t.start_time < ? AND t.end_time > ?
> ```
> If result: `"A class for this semester already exists in this time slot."`

> **RULE:** PHP pre-INSERT teacher clash check:
> ```sql
> SELECT t.id FROM timetables t
> JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
> WHERE ta.teacher_id = <teacher_id>
>   AND ta.id != <this_assignment_id>
>   AND t.day = ? AND t.start_time < ? AND t.end_time > ?
> ```
> If result: `"Teacher is already scheduled at this time."`

---

### Table 8 — `attendance`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `student_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→users.id CASCADE` | The student. PHP validates `role='STUDENT'`. |
| `timetable_slot_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→timetables.id CASCADE` | The recurring timetable slot. |
| `date` | `DATE` | `NOT NULL` | Calendar date of the session. |
| `status` | `ENUM('PRESENT','ABSENT')` | `NOT NULL` | Set by teacher. |
| `marked_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | Teacher who marked attendance. |
| `marked_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | Auto-set on INSERT. |

> **CRITICAL:** `UNIQUE KEY` on `(student_id, timetable_slot_id, date)`.

> **CRITICAL:** PHP attendance window enforcement (server-side only): fetch slot `start_time` and `end_time`. Get `GRACE_MINUTES` from `system_config`. Check: `current server time >= start_time` AND `current server time <= end_time + GRACE_MINUTES`. Use PHP `DateTime` comparisons. If outside window: return HTTP 403 `{"error":"Attendance window for this session is closed."}`.

> **RULE:** PHP validates: `date`'s day of week must match timetable slot's `day` column. Use `date('D', strtotime($date))` mapped to `MON/TUE/etc`.

---

### Table 9 — `student_fees`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `student_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→users.id CASCADE` | The student. |
| `semester_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→semesters.id CASCADE` | The semester. |
| `amount_paid` | `DECIMAL(10,2)` | `NOT NULL DEFAULT 0.00` | Updated by Accountant. PHP validates: `>= 0` AND `<= semester.fee_amount` using `bccomp()`. |

> **CRITICAL:** `UNIQUE KEY` on `(student_id, semester_id)`.

> **COMPUTED:**
> - `pending_amount`: PHP computes using BCMath: `bcsub($semester->fee_amount, $amount_paid, 2)`. If `fee_amount` is NULL: pending is null.
> - `fee_status`: `($pending == '0.00') ? "Paid" : "Pending"`. Use `bccomp($pending, '0.00', 2) === 0` for comparison.
> - **Never use PHP float arithmetic on money values.**

> **FLOW — Auto-creation Trigger 1:** After PHP INSERTs a `student_profile` row, PHP service function immediately INSERTs `student_fee` rows for all semesters `WHERE program_id = student's program AND is_current = 1`, each with `amount_paid=0.00`. Wrapped in PDO transaction.

> **FLOW — Auto-creation Trigger 2:** When semester is activated (`is_current` set to `1`), PHP service INSERTs `student_fee` rows for all active students (`users WHERE role=STUDENT AND is_active=1`) whose `student_profiles.program_id = this semester's program_id`, WHERE no existing `student_fee` row exists for `(student_id, semester_id)`. Wrapped in PDO transaction.

---

### Table 10 — `password_reset_requests`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `requested_by` | `BIGINT UNSIGNED` | `NOT NULL, FK→users.id CASCADE` | User who requested the reset. |
| `status` | `ENUM('PENDING','APPROVED','EXPIRED')` | `NOT NULL DEFAULT 'PENDING'` | Updated by approval or cron. |
| `created_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | When submitted. |
| `resolved_at` | `DATETIME` | `NULL` | When approved or expired. |
| `resolved_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | Authority who approved. NULL if expired. |

---

### Table 11 — `system_config`

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `config_key` | `VARCHAR(60)` | `NOT NULL UNIQUE` | Setting key. |
| `config_value` | `TEXT` | `NOT NULL` | Setting value as string. PHP parses per key. |
| `updated_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | Principal who last updated. |
| `updated_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Auto-updated. |

**Required seed rows:**

| `config_key` | Default Value | Description |
|---|---|---|
| `WORKING_DAYS` | `MON,TUE,WED,THU,FRI,SAT` | Comma-separated allowed days. |
| `DAY_START_TIME` | `09:00` | Earliest class start. Format: `HH:MM`. |
| `DAY_END_TIME` | `17:00` | Latest class end. Format: `HH:MM`. |
| `GRACE_MINUTES` | `15` | Minutes past `end_time` attendance can still be marked. |

---

### Table 12 — `audit_log`

> **Append-only. No UPDATE or DELETE ever.**

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `performed_by` | `BIGINT UNSIGNED` | `NULL, FK→users.id SET NULL` | User who performed the action. |
| `action` | `VARCHAR(60)` | `NOT NULL` | Action code. See full list below. |
| `target_table` | `VARCHAR(60)` | `NULL` | Affected table name. |
| `target_id` | `BIGINT UNSIGNED` | `NULL` | Affected record PK. |
| `metadata` | `JSON` | `NULL` | Extra context. `json_encode()` on INSERT. `json_decode()` on SELECT. |
| `timestamp` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | Auto-set on INSERT. Immutable. |

**Audit Log Action Codes:**

| Action Code | When It Fires |
|---|---|
| `USER_CREATED` | Any new account created (all roles). |
| `USER_DEACTIVATED` | Account set `is_active=0`. |
| `USER_REACTIVATED` | Account set `is_active=1`. |
| `PASSWORD_CHANGED` | User changes own password. |
| `PASSWORD_RESET_REQUESTED` | User submits forgot-password request. |
| `PASSWORD_RESET_APPROVED` | Authority approves reset request. |
| `PASSWORD_RESET_EXPIRED` | Background task expires a stale request. |
| `PROGRAM_CREATED` | VP creates a new program. |
| `PROGRAM_DEACTIVATED` | VP deactivates a program. |
| `SEMESTER_CREATED` | VP creates a semester. |
| `SEMESTER_ACTIVATED` | VP sets `is_current=1` for a semester. |
| `SUBJECT_CREATED` | VP creates a subject. |
| `TEACHER_ASSIGNED` | VP creates a TeacherAssignment. |
| `TIMETABLE_CREATED` | VP creates a timetable slot. |
| `STUDENT_ENROLLED` | Manager creates a student account. |
| `STUDENTS_BULK_IMPORTED` | Manager confirms a CSV import. metadata: `{total, imported, rejected}`. |
| `FEE_AMOUNT_SET` | Accountant sets `semester.fee_amount`. |
| `FEE_PAYMENT_RECORDED` | Accountant updates `StudentFee.amount_paid`. |
| `ATTENDANCE_MARKED` | Teacher marks attendance for a session. |
| `CONFIG_UPDATED` | Principal updates a SystemConfig value. |

---

### Table 13 — `jwt_blacklist`

Stores invalidated JWT token IDs. Checked on every protected request.

| Column | MySQL Type | Constraint | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | `AUTO_INCREMENT PK` | Primary key. |
| `jti` | `VARCHAR(255)` | `NOT NULL UNIQUE` | JWT ID claim from the token payload. Unique per token. |
| `user_id` | `BIGINT UNSIGNED` | `NOT NULL, FK→users.id CASCADE` | User whose token was blacklisted. |
| `expires_at` | `DATETIME` | `NOT NULL` | Original token expiry. Used for cleanup. |
| `created_at` | `DATETIME` | `DEFAULT CURRENT_TIMESTAMP` | When blacklisted. |

> **RULE:** On every protected API request: after decoding JWT, check `SELECT id FROM jwt_blacklist WHERE jti=?`. If found: return HTTP 401 `{"error":"Token has been revoked."}`.

> **RULE:** Periodic cleanup (can run in same cron job): `DELETE FROM jwt_blacklist WHERE expires_at < NOW()`. This keeps the table small.

---

### MySQL BEFORE UPDATE TRIGGER — Semester `is_current` Enforcement

This trigger **replaces** the PostgreSQL partial unique index which MySQL does not support. Run this SQL after creating the `semesters` table.

```sql
DELIMITER $$

CREATE TRIGGER enforce_single_current_semester
BEFORE UPDATE ON semesters FOR EACH ROW
BEGIN
    IF NEW.is_current = 1 AND OLD.is_current = 0 THEN
        UPDATE semesters
        SET    is_current = 0
        WHERE  program_id = NEW.program_id
          AND  id         != NEW.id;
    END IF;
END$$

DELIMITER ;
```

> **CRITICAL:** PHP must ALSO set `is_current=0` for other semesters of the same program inside a PDO transaction BEFORE setting `is_current=1` on the target. This double-enforcement ensures correctness even if the trigger is dropped or fails.

---

## PART 4 — PHP Implementation Rules

### 4.1 PDO Connection

- Single PDO connection created in `app/core/Database.php` using singleton pattern.
- DSN: `mysql:host=DB_HOST;dbname=DB_NAME;charset=utf8mb4`
- PDO options:
  ```php
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false
  ```
- On connect, execute:
  ```sql
  SET time_zone = '+00:00';
  SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
  ```
- All configuration (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `JWT_SECRET`, `APP_ENV`) loaded from `.env` file via `config/env.php`.

### 4.2 JWT Implementation

- Use `firebase/php-jwt`. JWT payload must include: `user_id` (int), `role` (string), `jti` (unique token ID — use `bin2hex(random_bytes(16))`), `iat` (issued at), `exp` (expiry).
- Access token exp: `time() + 3600`. Refresh token exp: `time() + 604800`.
- On every protected request:
  1. Extract Bearer token from `Authorization` header.
  2. Decode and verify with `firebase/php-jwt`.
  3. Check `jti` not in `jwt_blacklist`.
  4. Fetch user from DB by `user_id`.
  5. Check `is_active == 1`.
  6. Check `must_change_password == 0` (if `1`, return 403).
- Auth middleware function: `app/core/Auth.php` → `requireAuth($allowedRoles = [])`.
- Token refresh: `POST /api/auth/refresh` — verify refresh token, check blacklist, issue new access token. Do **NOT** rotate refresh token.

### 4.3 Password Hashing

```php
// Hash
password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])

// Verify
password_verify($inputPassword, $storedHash)

// Temp password generation (produces 10 hex chars)
bin2hex(random_bytes(5))
```

> **NEVER** log, return, or store plaintext passwords anywhere.

### 4.4 PHP Helper Functions (`app/helpers/computed.php`)

| Function | Signature | Returns |
|---|---|---|
| `compute_term` | `(int $semester_number): string` | `"Odd"` or `"Even"` |
| `compute_pending` | `(string\|null $fee_amount, string $amount_paid): string\|null` | Uses `bcsub($fee_amount, $amount_paid, 2)`. Returns null if `fee_amount` is null. |
| `compute_fee_status` | `(string\|null $pending): string` | Returns `"Paid"`, `"Pending"`, or `"Fee not set"` |
| `format_semester_response` | `(array $row): array` | Takes a DB row, appends `term`, returns the array. |
| `format_student_fee_response` | `(array $row): array` | Appends `pending_amount` and `fee_status`. |

### 4.5 Role-Based Access Control

- `app/core/Auth.php` provides: `requireAuth(array $roles): void`. Decodes JWT, validates, checks role. If role not in `$roles` array: return HTTP 403 `{"error":"Forbidden."}`.
- Usage at top of every controller method:
  ```php
  Auth::requireAuth(['VP', 'PRINCIPAL'])
  ```
- For endpoints accessible by all authenticated roles:
  ```php
  Auth::requireAuth([])  // validates JWT but does not restrict role
  ```

### 4.6 Routing

- `public/index.php` is the single entry point. Set `AllowOverride All` in Apache or use nginx rewrite.
- `.htaccess` (Apache):
  ```apache
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^ index.php [QSA,L]
  ```
- Router: `app/core/Router.php`. Supports GET, POST, PATCH, DELETE. Matches `/api/*` routes and returns JSON. Matches `/*` routes and renders PHP templates.
- API response format:
  ```php
  json_encode(['data' => ..., 'message' => ...])
  // or
  json_encode(['error' => ...])
  ```
  With appropriate HTTP status code via `http_response_code(N)`.
- Set `header('Content-Type: application/json')` for all API responses.
- Set `header('Content-Type: text/html; charset=utf-8')` for web responses.

### 4.7 AuditLog Utility

- `app/helpers/audit.php` provides:
  ```php
  log_action(
      int $performed_by,
      string $action,
      string|null $target_table,
      int|null $target_id,
      array|null $metadata
  ): void
  ```
- Wraps INSERT in `try/catch`. On any exception: write to `logs/audit_errors.log` and silently return. **NEVER** let logging failure break the primary operation.
- Call `log_action()` at the **END** of every controller method that performs a write operation.

### 4.8 CORS Headers

Add to `public/index.php` before routing, for all requests:

```php
header('Access-Control-Allow-Origin: *');  // adjust origin for production
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

### 4.9 File Uploads (CSV)

- Accept `.csv` files only. PHP validates: `$_FILES['csv']['type']` must be `text/csv` or `text/plain` or `application/vnd.ms-excel`.
- Move to `uploads/temp/` with a UUID filename. Process. Delete immediately after processing.
- `php.ini` (or runtime): `upload_max_filesize = 10M`, `post_max_size = 12M`.
- Use `fgetcsv()` to parse the CSV line by line. Validate every row before any INSERT.

### 4.10 Cron Script

- `cron/expire_reset_requests.php`: bootstraps the app (load `.env`, connect DB), runs the expiry SQL, logs result to `logs/cron.log` with timestamp.
- Crontab entry:
  ```cron
  0 0 * * * /usr/bin/php /var/www/ims/cron/expire_reset_requests.php >> /var/www/ims/logs/cron.log 2>&1
  ```
- Also in same cron or separate:
  ```sql
  DELETE FROM jwt_blacklist WHERE expires_at < NOW()
  ```
  to keep the blacklist table lean.

---

## PART 5 — API Endpoints

All endpoints prefixed `/api/`. All responses JSON. Protected endpoints require `Authorization: Bearer <token>` header.

> **NOTE:** Every protected endpoint middleware order:
> 1. Decode + verify JWT
> 2. Check `jti` not in `jwt_blacklist`
> 3. Fetch user by `user_id`
> 4. Check `is_active == 1`
> 5. Check `must_change_password == 0` (if `1`: return 403 `{"error":"Password change required.","redirect":"/change-password/"}`)
>
> Only after all pass does the endpoint logic run.

### 5.1 Authentication

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `POST` | `/api/auth/login` | Public | Body: `{"login_id","password"}`. Returns access + refresh tokens, `role`, `full_name`, `must_change_password`. |
| `POST` | `/api/auth/refresh` | Refresh token | Verify refresh JWT, check blacklist, issue new access token. |
| `POST` | `/api/auth/logout` | Authenticated | INSERT `jti` of refresh token into `jwt_blacklist`. |
| `POST` | `/api/auth/change-password` | Authenticated | Body: `{"current_password","new_password","confirm_password"}`. Hash new. Set `must_change_password=0`. Log `PASSWORD_CHANGED`. |
| `POST` | `/api/auth/forgot-password` | Public | Body: `{"login_id"}`. Creates `password_reset_requests` record. Always return 200 (do not reveal whether login_id exists). |

### 5.2 Dashboard

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/dashboard` | Authenticated | Returns role-specific stats object. |

**Response per role:**
- **PRINCIPAL:** `{ total_students, total_teachers, active_programs, pending_resets_count }`
- **VP:** `{ total_students, total_teachers, active_semesters_count, pending_teacher_resets }`
- **MANAGER:** `{ total_students, active_students, inactive_students, pending_student_resets }`
- **ACCOUNTANT:** `{ total_students, total_fee_collected, total_fee_pending, active_semesters_count }`
- **TEACHER:** `{ assigned_subjects_count, todays_slots: [{subject_name, start_time, end_time, semester_label}] }`
- **STUDENT:** `{ current_semester_label, attendance_percentage, fee_status, pending_amount }`

### 5.3 User Management

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/users?role=VP&is_active=1` | PRINCIPAL (VP/MANAGER/ACCOUNTANT), VP (TEACHER) | List users by role. |
| `POST` | `/api/users` | PRINCIPAL / VP | Create user. Required: `role`, `login_id`, `full_name`. Optional: `email`, `phone`. Log `USER_CREATED`. |
| `GET` | `/api/users/:id` | PRINCIPAL or VP | Get single user. |
| `PATCH` | `/api/users/:id` | PRINCIPAL | Update `full_name`, `email`, `phone` only. |
| `PATCH` | `/api/users/:id/toggle-active` | PRINCIPAL / VP | Flip `is_active`. On deactivate: blacklist tokens. Log `USER_DEACTIVATED` or `USER_REACTIVATED`. |

### 5.4 Password Reset Management

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/password-resets?status=PENDING` | PRINCIPAL / VP / MANAGER (scoped by role) | List pending requests within role's scope. |
| `POST` | `/api/password-resets/:id/approve` | PRINCIPAL / VP / MANAGER | Generate + hash temp password. Set `status=APPROVED`. Log `PASSWORD_RESET_APPROVED`. |

### 5.5 Programs

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/programs` | All authenticated | List all programs. |
| `POST` | `/api/programs` | VP | Fields: `program_name`, `program_code` (PHP `strtoupper`), `duration_semesters`. Log `PROGRAM_CREATED`. |
| `GET` | `/api/programs/:id` | All authenticated | Get single program. |
| `PATCH` | `/api/programs/:id/toggle-active` | VP | Pre-check: active students + current semesters. Block if any exist. Log `PROGRAM_DEACTIVATED`. |

### 5.6 Semesters

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/semesters?program_id=X&is_current=1` | All authenticated | Response includes computed `"term"` field. |
| `POST` | `/api/semesters` | VP | Validate `semester_number` range and `academic_year` format. Log `SEMESTER_CREATED`. |
| `PATCH` | `/api/semesters/:id/activate` | VP | PDO transaction: clear others → set `is_current=1` → auto-create StudentFee rows. Log `SEMESTER_ACTIVATED`. |
| `PATCH` | `/api/semesters/:id/set-fee` | ACCOUNTANT | UPDATE `fee_amount`. Log `FEE_AMOUNT_SET`. |

### 5.7 Subjects

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/subjects?semester_id=X` | All authenticated | List subjects. |
| `POST` | `/api/subjects` | VP | Validate `UNIQUE(semester_id, subject_code)`. Log `SUBJECT_CREATED`. |

### 5.8 Teacher Assignments

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/assignments?teacher_id=X` | VP (all), TEACHER (own), STUDENT (own semester) | List assignments. |
| `POST` | `/api/assignments` | VP | Validate `teacher role=TEACHER`. Run semester-uniqueness pre-INSERT check. Log `TEACHER_ASSIGNED`. |
| `DELETE` | `/api/assignments/:id` | VP | Remove assignment. |

### 5.9 Timetable

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/timetable?teacher_id=X&semester_id=Y&day=MON` | All authenticated (filtered) | List timetable slots. |
| `POST` | `/api/timetable` | VP | Run all three validation checks. Log `TIMETABLE_CREATED`. |
| `DELETE` | `/api/timetable/:id` | VP | Remove slot. |

### 5.10 Students

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/students?program_id=X&is_active=1` | MANAGER / PRINCIPAL / VP / ACCOUNTANT | Returns JOIN of `users + student_profiles + programs`. |
| `POST` | `/api/students` | MANAGER | PDO transaction: INSERT users → INSERT student_profiles → auto-create fee rows. Log `STUDENT_ENROLLED`. |
| `GET` | `/api/students/:id` | MANAGER / PRINCIPAL / VP / ACCOUNTANT | Get student detail. |
| `POST` | `/api/students/csv-validate` | MANAGER | Phase 1: validate all rows, return error list. No INSERT. |
| `POST` | `/api/students/csv-import` | MANAGER | Phase 2: INSERT valid rows. Log `STUDENTS_BULK_IMPORTED`. |

**CSV required columns:** `full_name`, `login_id`, `registration_number`, `date_of_birth` (YYYY-MM-DD), `program_code`, `email` (optional), `phone` (optional).

### 5.11 Fees

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/student-fees?student_id=X&semester_id=Y` | ACCOUNTANT (all), STUDENT (own) | Response includes computed `pending_amount` and `fee_status`. |
| `PATCH` | `/api/student-fees/:id` | ACCOUNTANT | Update `amount_paid`. Validate with BCMath. Log `FEE_PAYMENT_RECORDED`. |

### 5.12 Attendance

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/attendance/sessions` | TEACHER | Returns today's timetable slots for authenticated teacher. |
| `POST` | `/api/attendance/mark` | TEACHER | Body: `{timetable_slot_id, date, records:[{student_id, status}]}`. Validates window + teacher ownership + date/day match. Log `ATTENDANCE_MARKED`. |
| `GET` | `/api/attendance?student_id=X&semester_id=Y` | TEACHER (own), STUDENT (own), PRINCIPAL / VP / MANAGER (all) | List attendance records. |

### 5.13 Profile

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/profile` | Authenticated (own) | Returns own user row. |
| `PATCH` | `/api/profile` | Authenticated (own) | Update `full_name`, `email`, `phone` only. |

### 5.14 Audit Log

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/audit-log?performed_by=X&action=Y&date_from=Z&date_to=W` | PRINCIPAL only | 50 per page. `ORDER BY timestamp DESC`. |

### 5.15 System Config

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/config` | PRINCIPAL | List all config keys. |
| `PATCH` | `/api/config/:config_key` | PRINCIPAL | Validate format. UPDATE `system_config`. Log `CONFIG_UPDATED`. |

---

## PART 6 — Frontend UI Specification

### 6.1 Global Design Rules

- **Framework:** Bootstrap 5.3. Custom overrides in `public/assets/css/ims.css` only.
- **Colors:**
  - Background: `#FFFFFF`
  - Sidebar: `#F9FAFB`
  - Card borders: `1px solid #E5E7EB`
  - Primary buttons: `#1B3A6B`
  - Secondary buttons: `#6B7280`
  - Danger buttons: `#DC2626`
  - Focus outline: `2px solid #2563A8`
- **Typography:** Inter, Segoe UI, Arial system stack. Body 14px. Headings 16–24px. Regular and semibold weights only.
- **Layout:** Fixed left sidebar 240px + main content. Sidebar collapses to icons on mobile. Top navbar shows user name and logout.
- No dark mode. Hover transitions 150ms only.
- All tables: Bootstrap `table-hover`, header row `#F3F4F6`, search input, filter dropdowns, 25-row pagination, "No records found" empty state.
- **Status badges:** Active=green pill, Inactive=gray pill, Paid=green, Pending=amber, Present=green, Absent=red, Current=blue, Past=gray.
- PHP templates: `layouts/base.php` contains HTML shell. Each screen uses `ob_start()` + include layout, or simple `require` of header/footer partials.
- All AJAX uses Fetch API with `Authorization: Bearer <token>` from `localStorage`. On 401: attempt token refresh. If refresh fails: redirect to `/login`.

### 6.2 Auth Screens

**Login — `/login`**
- Login ID + Password (show/hide toggle). `POST /api/auth/login`. Store tokens in `localStorage`. Check `must_change_password` flag → redirect to `/change-password` if true.

**Change Password — `/change-password`**
- Current password, new password, confirm. Strength indicator (min 8 chars + 1 number). `POST /api/auth/change-password`. On success: redirect to `/dashboard`.

### 6.3 Principal Screens

| Route | Description |
|---|---|
| `/principal/dashboard` | 4 stat cards: Total Students, Total Teachers, Active Programs, Pending Resets. Pending resets table with Approve button. |
| `/principal/accounts` | Tabs: VP \| Manager \| Accountant. Table per tab with toggle active. Side drawer for Add Account form. |
| `/principal/students` | Read-only table: Reg No, Name, Program, Status. Filters: Program, Status. Click → detail. |
| `/principal/teachers` | Read-only: Staff ID, Name, Email, Status. Click → detail with assignments. |
| `/principal/config` | 4 fields: Working Days (checkboxes), Start Time, End Time, Grace Minutes. Save per key. |
| `/principal/audit-log` | Table: Timestamp, Performed By, Action, Target, Details. Date range + action type + role filters. |

### 6.4 Vice Principal Screens

| Route | Description |
|---|---|
| `/vp/dashboard` | 4 stat cards. Pending teacher reset requests table. |
| `/vp/programs` | Table + Add Program drawer. Deactivate with pre-check dialog showing counts. |
| `/vp/semesters` | Filter by program. Table shows computed Term badge. Activate button. Add Semester drawer. |
| `/vp/subjects` | Filter by semester. Table shows Assigned Teacher or Unassigned badge. |
| `/vp/teachers` | Table + Add Teacher drawer. Activate/Deactivate. View Assignments. |
| `/vp/assignments` | Table + Add Assignment drawer. Teacher dropdown + Subject dropdown (filtered by semester). |
| `/vp/timetable` | Grid view (days=columns, time=rows) and list view toggle. Add Slot form with real-time clash pre-check. |

### 6.5 Manager Screens

| Route | Description |
|---|---|
| `/manager/dashboard` | 4 stat cards. Pending student reset requests table with Approve. |
| `/manager/students` | Table + Add Student drawer. Filters: Program, Status. Student detail page with fee + attendance summary. |
| `/manager/students/csv-upload` | Step 1 Upload → Step 2 Preview errors + Download Error CSV → Step 3 Confirm Import → Step 4 Result summary. |

### 6.6 Accountant Screens

| Route | Description |
|---|---|
| `/accountant/dashboard` | 4 stat cards: Total Collected, Total Pending, Active Semesters, Total Students. |
| `/accountant/semester-fees` | Edit semester fee amounts and review all program/semester fee settings in a single table. |
| `/accountant/student-fees` | Filter student fees by program, semester, registration number, and status; update payments inline. |

### 6.7 Teacher Screens

| Route | Description |
|---|---|
| `/teacher/dashboard` | Today's schedule cards. Mark Attendance button active only within window (disabled with tooltip otherwise). My Subjects list. |
| `/teacher/attendance/mark/:slot_id` | Class info header. Student list with Present/Absent radio buttons. Mark All Present / Absent buttons. Submit. |
| `/teacher/attendance/history` | Filter by subject + date range. Session-level stats table. |

### 6.8 Student Screens

| Route | Description |
|---|---|
| `/student/dashboard` | 4 stat cards: Current Semester, Attendance %, Fee Status, Pending Amount. |
| `/student/timetable` | Read-only weekly grid for current semester. |
| `/student/attendance` | Subject-level table with expand for session detail. |
| `/student/fees` | Per-semester fee table with computed pending and status. |
| `/student/profile` | View info. Edit email/phone. Change Password link. Request Password Reset button. |

---

## PART 7 — Complete Business Rules

> All rules enforced at both PHP application layer and MySQL layer. No exceptions.

### 7.1 Identity & Auth

1. `login_id` globally unique. MySQL `UNIQUE` constraint. PHP pre-INSERT check.
2. `role` immutable after INSERT. No PHP endpoint may `UPDATE` the `role` column.
3. Passwords: PHP bcrypt hash only. Plaintext never stored, logged, or returned.
4. JWT access: 3600s. Refresh: 604800s. `jti` claim unique per token.
5. Every protected request: decode JWT → check blacklist → fetch user → `is_active==1` → `must_change_password==0`.
6. On deactivation: blacklist all user's active tokens by inserting their `jti` values.
7. Temp password: `bin2hex(random_bytes(5))` = 10 hex characters.

### 7.2 Programs

8. `program_code`: PHP `strtoupper()` before INSERT. Regex: `/^[A-Z]{2,10}$/`. MySQL `UNIQUE`.
9. `duration_semesters`: PHP validates `>= 1`.
10. Cannot deactivate if active students or current semesters exist. PHP pre-check.
11. Deactivated programs: no new semesters or students. PHP validation on create.

### 7.3 Semesters

12. `semester_number`: PHP validates `>= 1` AND `<= program.duration_semesters`.
13. `academic_year`: PHP validates `YYYY-YYYY` format. PHP validates `year2 == year1 + 1`.
14. MySQL `UNIQUE KEY (program_id, semester_number, academic_year)`.
15. Only one `is_current=1` per program. Enforced by MySQL trigger AND PHP transaction.
16. `term`: never stored. PHP: `($n % 2 == 1) ? "Odd" : "Even"`.
17. `fee_amount` NULL means not configured. UI shows "Fee not set".

### 7.4 Subjects & Assignments

18. MySQL `UNIQUE (semester_id, subject_code)`. Same code allowed across semesters.
19. MySQL `UNIQUE (subject_id)` in `teacher_assignments`. One teacher per subject.
20. PHP pre-INSERT: teacher cannot have two assignments in the same semester.
21. `semester_id` NOT stored in `teacher_assignments`. Derived via JOIN.

### 7.5 Timetable

22. `day`: MySQL ENUM validation + PHP validates against `WORKING_DAYS` config.
23. `start_time >= DAY_START_TIME` config. `end_time <= DAY_END_TIME` config.
24. `end_time` must be `> start_time`. PHP validates before INSERT.
25. Semester clash: PHP pre-INSERT query. Teacher clash: PHP pre-INSERT query.
26. Overlap: `new_start < existing_end AND new_end > existing_start`.

### 7.6 Attendance

27. MySQL `UNIQUE (student_id, timetable_slot_id, date)`.
28. Window: `server_now >= start_time` AND `server_now <= end_time + GRACE_MINUTES`. PHP `DateTime`. Server-side only.
29. Date day-of-week must match slot `day`. PHP: `date('N', strtotime($date))` mapped to day name.
30. Teacher can only mark own slots. PHP validates `teacher_assignments.teacher_id == auth user`.
31. Students in session: all active students (`is_active=1`) in the semester of the timetable slot.

### 7.7 Fees

32. MySQL `UNIQUE (student_id, semester_id)`.
33. `amount_paid`: PHP BCMath validates `bccomp($paid,'0.00',2) >= 0` AND `bccomp($paid,$fee_amount,2) <= 0`.
34. `pending_amount`: `bcsub($fee_amount, $amount_paid, 2)`. Never stored.
35. `fee_status`: `bccomp($pending,'0.00',2) === 0 ? "Paid" : "Pending"`. Never stored.
36. StudentFee auto-created on enrollment AND on semester activation. PDO transaction.

### 7.8 Password Resets

37. One `PENDING` request per user at a time. PHP validates before INSERT.
38. 7-day expiry via cron script. Idempotent (`WHERE status=PENDING`).
39. On approval: bcrypt hash of new temp password. `must_change_password=1`.
40. Approval chain: `STUDENT→MANAGER`, `TEACHER→VP`, `VP/MANAGER/ACCOUNTANT→PRINCIPAL`.

### 7.9 Student Program

41. `program_id` read-only after INSERT. No PHP endpoint UPDATEs it.
42. Program change: deactivate old account + create new. Policy enforced by no UPDATE endpoint.

### 7.10 AuditLog

43. Append-only. No PHP code issues `UPDATE` or `DELETE` on `audit_log`.
44. All 20 action codes listed in Part 3 must produce a log entry.
45. Viewable only by `role=PRINCIPAL`.
46. `log_action()` silently swallows exceptions. Never breaks primary operation.

---

## PART 8 — Identified Loopholes and Issues (PHP + MySQL)

> These are all issues that arise specifically from migrating from Django + PostgreSQL to PHP + MySQL. Every issue below has a concrete resolution already incorporated into this specification.

### Issue 1 — MySQL Has No Partial Unique Index `[SEVERITY: HIGH]`

**Problem:** PostgreSQL supports `CREATE UNIQUE INDEX ... WHERE is_current=True`. MySQL 8.0 does NOT support partial (filtered) unique indexes. Without this, two semesters in the same program could both have `is_current=1` if a race condition or direct SQL insert bypasses application logic.

**Resolution:** MySQL BEFORE UPDATE TRIGGER (`enforce_single_current_semester`) fires on every UPDATE to the `semesters` table and clears other `is_current` values for the same program atomically. PHP also runs a transaction that clears others before setting the new one. Double-enforcement covers both application and DB paths. NEVER set `is_current=1` on INSERT — only allow activation via the `/activate` endpoint (always an UPDATE).

---

### Issue 2 — No ORM / No Django Signals `[SEVERITY: HIGH]`

**Problem:** Django signals (`pre_save`, `post_save`) automatically fire side-effects. PHP has no equivalent. Missing a manual call means student fees are not auto-created or `is_current` is not cleared.

**Resolution:** Every side-effect that was a Django signal is now an explicit PHP service function call within the same PDO transaction. StudentFee auto-creation must be called explicitly in two places: (a) after student INSERT in `POST /api/students`, (b) after semester activation in `PATCH /api/semesters/:id/activate`. Both wrapped in PDO transactions.

---

### Issue 3 — No Django `@property` — Computed Values Can Be Forgotten `[SEVERITY: HIGH]`

**Problem:** Django `@property` on models ensures `term`, `pending_amount`, `fee_status` are always computed when the model is accessed. In PHP raw queries, it is easy to forget to compute and include these in a response.

**Resolution:** Central PHP helper functions in `app/helpers/computed.php`. Every controller that returns semester data MUST call `format_semester_response()`. Every controller returning `student_fees` MUST call `format_student_fee_response()`. These are mandatory, not optional.

---

### Issue 4 — TINYINT(1) vs PHP Boolean Confusion `[SEVERITY: MEDIUM]`

**Problem:** MySQL `TINYINT(1)` returns the string `"1"` or `"0"` from PDO, not PHP `true`/`false`. Strict comparisons (`$row["is_active"] === true`) will silently fail, causing deactivated accounts to appear active.

**Resolution:** All boolean comparisons in PHP must use `$row["is_active"] == 1` (not `=== true`). All boolean outputs in JSON must cast explicitly: `"is_active": (bool)$row["is_active"]`.

---

### Issue 5 — Float Arithmetic on Money Values `[SEVERITY: MEDIUM]`

**Problem:** PHP float arithmetic is imprecise. `5000.00 - 4999.99` may not equal `0.01` exactly. Using native PHP math on `fee_amount` and `amount_paid` will produce incorrect `pending_amount` values and wrong Paid/Pending status.

**Resolution:** All fee arithmetic uses PHP BCMath extension: `bcsub($fee, $paid, 2)`, `bccomp($pending, "0.00", 2)`. PDO returns `DECIMAL` columns as strings from MySQL, which is correct input for BCMath. Never cast fee values to float.

---

### Issue 6 — MySQL Strict Mode Not Enabled by Default `[SEVERITY: HIGH]`

**Problem:** MySQL in non-strict mode silently truncates VARCHAR values, inserts zero dates, and ignores type errors. This means invalid data can enter the DB without any error from PHP.

**Resolution:** Set `sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION` in `my.cnf` AND in the PDO connection after connect. Both locations required.

---

### Issue 7 — Character Encoding Corruption `[SEVERITY: HIGH]`

**Problem:** MySQL default charset may be `latin1` or `utf8` (not `utf8mb4`). Institution names, student names, and subject names with special characters will be corrupted silently.

**Resolution:** All tables: `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. PDO DSN: `charset=utf8mb4`. MySQL server `my.cnf`: `character-set-server=utf8mb4`, `collation-server=utf8mb4_unicode_ci`.

---

### Issue 8 — JWT Token Blacklist Not in Original Spec `[SEVERITY: HIGH]`

**Problem:** The original spec used simplejwt's built-in `BlacklistedToken` table. PHP has no equivalent built-in. Without a blacklist, deactivated users whose tokens have not expired continue to have valid access for up to 60 minutes.

**Resolution:** New `jwt_blacklist` table (Table 13 in this spec). On logout and deactivation: INSERT `jti` of all user tokens into `jwt_blacklist`. Every protected request checks `jti` against this table. Periodic cron cleanup deletes expired entries.

---

### Issue 9 — CSV Upload Security `[SEVERITY: HIGH]`

**Problem:** PHP file upload endpoints are a common attack vector. Without MIME validation and proper temp file handling, malicious files can be uploaded and executed.

**Resolution:** Validate `$_FILES["csv"]["type"]`. Store in non-web-accessible `uploads/temp/` with UUID filename. Process with `fgetcsv()`. Delete immediately after processing. Set `upload_max_filesize=10M`. Never use the original filename from the upload.

---

### Issue 10 — SQL Injection if Raw Queries Are Used Improperly `[SEVERITY: HIGH]`

**Problem:** Unlike Django ORM which automatically parameterizes all queries, PHP PDO requires the developer to explicitly use prepared statements. Any string interpolation in SQL (e.g., `"WHERE id=$id"`) is a critical vulnerability.

**Resolution:** ALL SQL values must be PDO bound parameters. No string interpolation in SQL ever. Code review must reject any SQL with variable interpolation.

---

### Issue 11 — Timezone Mismatch `[SEVERITY: MEDIUM]`

**Problem:** PHP `date()` and MySQL `NOW()` may use different timezones. Attendance window checks comparing PHP `time()` against MySQL `TIME` fields can be off by hours if timezones differ.

**Resolution:** MySQL connection: `SET time_zone = '+00:00'`. PHP: `date_default_timezone_set('UTC')` in bootstrap. All server times are UTC. UI converts to local time for display only.

---

### Issue 12 — CORS Not Built-In to PHP `[SEVERITY: MEDIUM]`

**Problem:** Django had `django-cors-headers`. PHP has no automatic CORS handling. Browser AJAX calls from the frontend will fail on OPTIONS preflight if CORS headers are missing.

**Resolution:** `public/index.php` adds CORS headers for all responses before routing. OPTIONS requests return 200 immediately.

---

### Issue 13 — No Equivalent of Django Admin for Principal Seed `[SEVERITY: LOW]`

**Problem:** Django had `manage.py shell` or Django admin to create the Principal account. PHP has no equivalent built-in.

**Resolution:** `database/seeds/create_principal.php` is a PHP CLI script that prompts for `login_id` and `full_name`, generates a bcrypt-hashed temp password, and INSERTs the Principal. Run once:
```bash
php database/seeds/create_principal.php
```

---

### Issue 14 — Background Task Reliability `[SEVERITY: LOW]`

**Problem:** PHP CLI cron scripts can fail silently. Unlike Django management commands which have built-in error handling, a PHP cron script crash may not be noticed.

**Resolution:** Cron script wraps all logic in `try/catch`. Errors written to `logs/cron_errors.log` with timestamp. Script is idempotent (`WHERE status=PENDING` means re-running does no harm). Crontab redirects stderr to log file.

---

## PART 9 — Project Folder Structure

Every file and folder listed. The structure is organized for clarity, maintainability, and clean separation of concerns.

```
ims/
│
├── .env                              # DB_HOST, DB_NAME, DB_USER, DB_PASS, JWT_SECRET, APP_ENV, APP_URL. Never commit.
├── .env.example                      # Template for .env with empty values. Committed to git.
├── .gitignore                        # Excludes .env, vendor/, uploads/temp/, logs/
├── composer.json                     # Requires: firebase/php-jwt. PSR-4 autoload: App → app/
├── composer.lock                     # Lock file. Committed to git.
├── README.md                         # This file.
│
├── public/                           # Web root. Apache/Nginx document root points here.
│   ├── index.php                     # Single entry point. CORS headers → Router → dispatch.
│   ├── .htaccess                     # Apache rewrite: all requests → index.php
│   └── assets/
│       ├── css/
│       │   └── ims.css               # Custom Bootstrap overrides. IMS colour palette and layout.
│       └── js/
│           ├── app.js                # Global: apiFetch() with JWT auth + auto-refresh, logout, redirect helpers.
│           ├── auth.js               # Login, change-password, forgot-password form JS.
│           ├── tables.js             # Reusable: client-side search, pagination, empty state.
│           └── drawer.js             # Side drawer open/close logic for all Add forms.
│
├── app/
│   ├── core/
│   │   ├── Router.php                # HTTP router. GET/POST/PATCH/DELETE. Dispatches to controllers.
│   │   ├── Database.php              # PDO singleton. Sets charset, sql_mode, timezone on every connect.
│   │   ├── Auth.php                  # requireAuth(array $roles). JWT decode → blacklist check → is_active → must_change_password.
│   │   ├── Request.php               # Parses method, URI, body (json_decode), files, headers.
│   │   └── Response.php              # json($data, $status): Content-Type + http_response_code + json_encode + exit.
│   │
│   ├── controllers/
│   │   ├── AuthController.php        # login, refresh, logout, changePassword, forgotPassword.
│   │   ├── DashboardController.php   # index — role-specific stats.
│   │   ├── UserController.php        # index, store, show, update, toggleActive.
│   │   ├── PasswordResetController.php # index, approve.
│   │   ├── ProgramController.php     # index, store, show, toggleActive.
│   │   ├── SemesterController.php    # index, store, show, activate, setFee.
│   │   ├── SubjectController.php     # index, store, show.
│   │   ├── TeacherAssignmentController.php # index, store, destroy.
│   │   ├── TimetableController.php   # index, store, destroy.
│   │   ├── StudentController.php     # index, store, show, csvValidate, csvImport.
│   │   ├── StudentFeeController.php  # index, update.
│   │   ├── AttendanceController.php  # sessions, mark, index.
│   │   ├── ProfileController.php     # show, update.
│   │   ├── AuditLogController.php    # index — paginated, filtered.
│   │   └── ConfigController.php      # index, update.
│   │
│   ├── services/
│   │   ├── AuthService.php           # generateTokens(), hashPassword(), generateTempPassword(), blacklistToken().
│   │   ├── StudentFeeService.php     # createFeesForNewStudent(), createFeesOnSemesterActivation(). PDO transactions.
│   │   ├── SemesterService.php       # activate(): is_current clear + auto-fee creation in one transaction.
│   │   ├── CsvService.php            # validateCsv(), importCsv(). File reading, row validation, error reporting.
│   │   └── TimetableService.php      # checkSemesterClash(), checkTeacherClash(). Returns error string or null.
│   │
│   └── helpers/
│       ├── computed.php              # compute_term(), compute_pending(), compute_fee_status(), format_semester_response(), format_student_fee_response().
│       ├── audit.php                 # log_action(). Silent fail. INSERT into audit_log.
│       ├── validators.php            # validate_academic_year(), validate_program_code(), validate_time_format(), validate_date_format().
│       └── config.php                # get_config(string $key). Queries system_config. Caches in static variable per request.
│
├── templates/
│   ├── layouts/
│   │   └── base.php                  # Base HTML: <html>, <head> (Bootstrap CDN, ims.css), sidebar, navbar, content block, JS includes.
│   │
│   ├── auth/
│   │   ├── login.php                 # Login form. No sidebar.
│   │   └── change_password.php       # Change password form.
│   │
│   ├── principal/
│   │   ├── dashboard.php
│   │   ├── accounts.php              # Account management with tabs.
│   │   ├── students.php              # Read-only student list.
│   │   ├── teachers.php              # Read-only teacher list.
│   │   ├── config.php                # System config form.
│   │   └── audit_log.php             # Audit log table with filters.
│   │
│   ├── vp/
│   │   ├── dashboard.php
│   │   ├── programs.php
│   │   ├── semesters.php
│   │   ├── subjects.php
│   │   ├── teachers.php
│   │   ├── assignments.php
│   │   └── timetable.php             # Grid + list views.
│   │
│   ├── manager/
│   │   ├── dashboard.php
│   │   ├── students.php              # Student list + Add drawer.
│   │   ├── student_detail.php        # Info, fees, attendance.
│   │   └── csv_upload.php            # 4-step CSV upload flow.
│   │
│   ├── accountant/
│   │   ├── dashboard.php
│   │   └── fees.php                  # Two-panel fee management.
│   │
│   ├── teacher/
│   │   ├── dashboard.php
│   │   ├── mark_attendance.php
│   │   └── attendance_history.php
│   │
│   └── student/
│       ├── dashboard.php
│       ├── timetable.php
│       ├── attendance.php
│       ├── fees.php
│       └── profile.php
│
├── database/
│   ├── migrations/                   # SQL files. Run in filename order.
│   │   ├── 001_create_users.sql
│   │   ├── 002_create_student_profiles.sql
│   │   ├── 003_create_programs.sql
│   │   ├── 004_create_semesters.sql
│   │   ├── 005_create_semester_trigger.sql   # CREATE TRIGGER enforce_single_current_semester
│   │   ├── 006_create_subjects.sql
│   │   ├── 007_create_teacher_assignments.sql
│   │   ├── 008_create_timetables.sql
│   │   ├── 009_create_attendance.sql
│   │   ├── 010_create_student_fees.sql
│   │   ├── 011_create_password_reset_requests.sql
│   │   ├── 012_create_system_config.sql
│   │   ├── 013_create_audit_log.sql
│   │   └── 014_create_jwt_blacklist.sql
│   │
│   └── seeds/
│       ├── seed_system_config.sql    # INSERT 4 default system_config rows.
│       └── create_principal.php      # PHP CLI: prompts login_id + full_name, bcrypt hash, INSERTs Principal.
│
├── cron/
│   └── expire_reset_requests.php     # Expires PENDING reset requests. Cleans jwt_blacklist. Logs to logs/cron.log.
│
├── config/
│   ├── env.php                       # Loads .env file. Exposes $_ENV variables.
│   └── routes.php                    # Registers all API and web routes. Imported by public/index.php.
│
├── uploads/
│   └── temp/                         # NOT web-accessible. CSV temp files. Deleted after processing.
│
├── logs/                             # NOT web-accessible.
│   ├── cron.log                      # Output from cron runs.
│   ├── cron_errors.log               # Cron script exceptions.
│   └── audit_errors.log             # Exceptions from log_action().
│
└── vendor/                           # Composer dependencies. Auto-generated. In .gitignore.
```

---

## PART 10 — AI Agent Implementation Prompt

> Copy the following prompt verbatim and give it to the AI coding agent along with this document.

---

### — PROMPT START —

You are a senior full-stack developer. You have been given a complete technical specification document for the Institution Management System (IMS) built with PHP 8.2+ and MySQL 8.0+. Read every section of the document completely before writing any code. Implement exactly what is specified — nothing more, nothing less.

#### BACKEND — PHP + MySQL

1. Create the project folder structure exactly as specified in Part 9. Every folder and file listed must be created.
2. Implement all 14 MySQL tables exactly as specified in Part 3. Every column name, MySQL type, constraint, ENUM value, and FK relationship must match exactly. All tables: `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.
3. Implement the MySQL BEFORE UPDATE TRIGGER for `is_current` enforcement as specified in Part 3. Place the SQL in `database/migrations/005_create_semester_trigger.sql`.
4. Use PDO exclusively for all database operations. Every SQL value must be a bound parameter. No string interpolation in SQL under any circumstances. Use `FETCH_ASSOC` mode.
5. Set these on every PDO connection after connecting: `SET time_zone = '+00:00';` and `SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';`
6. Implement JWT using `firebase/php-jwt`. JWT payload must include: `user_id`, `role`, `jti` (`bin2hex(random_bytes(16))`), `iat`, `exp`. Access token: 3600s. Refresh: 604800s.
7. Implement password hashing with `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])` and `password_verify()`.
8. Implement `Auth::requireAuth(array $roles)` in `app/core/Auth.php`. It must: (1) decode JWT, (2) check `jti` not in `jwt_blacklist`, (3) fetch user from DB, (4) check `is_active==1`, (5) check `must_change_password==0`. Abort with correct HTTP code and JSON error at each failed check.
9. Implement all helper functions in `app/helpers/computed.php`: `compute_term()`, `compute_pending()` using `bcsub()` with scale 2, `compute_fee_status()` using `bccomp()`. Call these in every controller response that returns semester or fee data.
10. Implement `log_action()` in `app/helpers/audit.php`. Wrap in `try/catch`. Never let it throw. Call it from every controller that performs a write operation, using the action codes from Part 3.
11. Implement `StudentFeeService` with `createFeesForNewStudent()` and `createFeesOnSemesterActivation()`. Both must use PDO transactions (`beginTransaction / commit / rollBack`).
12. Implement `SemesterService::activate()`: inside one PDO transaction: (1) `UPDATE semesters SET is_current=0 WHERE program_id=X AND id!=Y`, (2) `UPDATE semesters SET is_current=1 WHERE id=Y`, (3) call `StudentFeeService::createFeesOnSemesterActivation()`.
13. Implement all API endpoints listed in Part 5. Each endpoint must call `Auth::requireAuth()` with the correct role array. Return JSON only for `/api/*` routes.
14. Implement the two-phase CSV upload: `POST /api/students/csv-validate` (validate only, no INSERT) and `POST /api/students/csv-import` (INSERT valid rows, log `STUDENTS_BULK_IMPORTED`).
15. Implement timetable clash checks in `TimetableService` before every INSERT. Semester clash check and teacher overlap check are both required. Overlap: `new_start < existing_end AND new_end > existing_start`.
16. Implement attendance window enforcement in `AttendanceController::mark()`. Use PHP `DateTime` to compare current server UTC time against slot `start_time` and `end_time + GRACE_MINUTES` from `system_config`.
17. Implement the cron script in `cron/expire_reset_requests.php`. It must expire stale PENDING requests and clean `jwt_blacklist` rows where `expires_at < NOW()`. Log results to `logs/cron.log`.
18. Implement the Principal seed script in `database/seeds/create_principal.php`. PHP CLI only. Prompts for `login_id` and `full_name`. Generates temp password. Hashes with bcrypt. INSERTs into `users` table.
19. Create `database/seeds/seed_system_config.sql` with the 4 default rows for `system_config`.
20. Implement `get_config(string $key)` in `app/helpers/config.php`. Must cache result per request in a static variable.
21. Add CORS headers in `public/index.php` before routing. Handle OPTIONS preflight.
22. Create `composer.json` requiring only `firebase/php-jwt`. Set up PSR-4 autoload for `App` namespace mapping to `app/`.

#### FRONTEND — PHP Templates + Bootstrap 5

23. Use native PHP templates in `templates/`. Create `layouts/base.php` as the shared HTML shell. Each screen file uses `require` to load header/sidebar partial and footer partial.
24. Implement every screen listed in Part 6. Every screen must match the specified layout, fields, filters, and interactions exactly.
25. Create `public/assets/css/ims.css` with the IMS color palette: background `#FFFFFF`, sidebar `#F9FAFB`, borders `#E5E7EB`, primary buttons `#1B3A6B`, secondary `#6B7280`, danger `#DC2626`, focus ring `#2563A8`.
26. Create `public/assets/js/app.js` with a global `apiFetch()` function that adds `Authorization: Bearer <token>` header, catches 401, attempts token refresh via `POST /api/auth/refresh`, retries original request once, and redirects to `/login` if refresh fails.
27. All status badges must use pill-style `<span>` elements: green for Active/Paid/Present, gray for Inactive, amber for Pending, red for Absent, blue for Current semester, gray for Past.
28. All data tables must include: search input (triggers JS filter on table rows), filter dropdowns, Bootstrap pagination at 25 rows, and a "No records found" empty state with an icon.
29. All forms must display field-level validation errors returned from the API, shown below the relevant input field in red text.
30. Add-forms must use a slide-in side drawer (`position:fixed, right-0`) — not Bootstrap modals.
31. The timetable screen must have two view toggle buttons: Grid View (HTML table, days=columns, times=rows) and List View (flat table). Both views on the same page, toggled with JS show/hide.
32. The CSV upload screen must be a 4-step flow: Step 1 (file input + template download), Step 2 (preview: valid count, error table, download error CSV button, Confirm/Cancel), Step 3 (loading), Step 4 (result: imported count, rejected count, Done button).
33. Teacher dashboard: Mark Attendance button is disabled with tooltip "Window not open" when current time is outside the slot's attendance window. Use JS to check time and toggle button state on page load and every minute.
34. Student profile page: "Request Password Reset" button calls `POST /api/auth/forgot-password` with the logged-in user's `login_id`. Show success message "Request submitted. Your manager will be notified."

#### GENERAL RULES

35. Do not implement any feature not specified in this document.
36. Do not use any PHP framework (no Laravel, no Symfony, no CodeIgniter). Pure PHP 8.2+ only.
37. Do not use any JavaScript framework (no React, Vue, Angular). Vanilla JS + Bootstrap 5 only.
38. Do not use PHP sessions for auth. JWT only. No `session_start()` anywhere.
39. Never cast fee values to PHP float. Use BCMath (`bcsub`, `bccomp`) for all money arithmetic.
40. Always cast MySQL `TINYINT` boolean columns in PHP: compare with `== 1` (not `=== true`). Cast to `(bool)` for JSON output.
41. Create a `README.md` with: requirements (PHP 8.2+, MySQL 8.0+, Composer), setup steps (clone, composer install, create DB, configure `.env`, run migration SQL files in order, run `seed_system_config.sql`, run `create_principal.php`, configure Apache/Nginx, set crontab), and development tips.
42. Write all code organized by file path. Use the format: `// FILE: ims/relative/path/to/file.php` followed immediately by the complete file contents.
43. Every SQL file in `database/migrations/` must contain only the SQL for that one table or migration object. No combining multiple tables in one file.

### — PROMPT END —

> **NOTE:** Give the AI agent this complete document AND the prompt above. The document is the specification. The prompt is the instruction. The agent must read the full document before writing any code.
