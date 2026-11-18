# 02 — SOFTWARE REQUIREMENTS SPECIFICATION (SRS)

| Metadata | Value |
|---|---|
| Document ID | DOC-02 |
| Version | 1.3.0 |
| Status | Draft |
| Target | Laravel Blade Web Application |
| Parent | `01_PRD.md` |

## 1. Purpose and conventions

This SRS converts product requirements into testable system requirements. Requirement identifiers are stable references; refinements should preserve IDs where semantics remain unchanged.

Priority: `MUST`, `SHOULD`, `COULD`. Status: `USER-DECIDED`, `SOURCE-DEFINED`, `PROPOSED`, `TBD`.

## 2. System context

The system is a server-rendered Laravel Full-Stack application. Browser requests are handled by Nginx and Laravel. Laravel authenticates users with sessions, applies policies and validation, performs application operations, and reads/writes MySQL. Browser-side JavaScript enhances interaction but is not an authority for security or business rules.

## 3. User classes

| Role | Description |
|---|---|
| Visitor | Can access login and student registration pages only. |
| Mahasiswa | Own personal tasks, own study activities, own academic progress, enrolled courses, own notifications. |
| Guru/Dosen | Assigned courses, academic task definitions, progress monitoring/correction within assigned courses, own notifications. |
| Admin | Student and lecturer accounts, enrollment, course administration, read-only academic monitoring, own notifications. |

## 4. Functional requirements

### 4.1 Authentication and account

| ID | Requirement | Priority |
|---|---|---|
| SRS-AUTH-001 | The system shall allow a visitor to register a Mahasiswa account. | MUST |
| SRS-AUTH-002 | Registration shall validate required identity and credential fields on the server. | MUST |
| SRS-AUTH-003 | The system shall authenticate using username and password. | MUST |
| SRS-AUTH-004 | Successful login shall create a Laravel authenticated session. | MUST |
| SRS-AUTH-005 | Logout shall invalidate the current session and rotate/invalidate the session token. | MUST |
| SRS-AUTH-006 | Authenticated routes shall require a valid session. | MUST |
| SRS-AUTH-007 | Role and resource authorization shall be enforced server-side. | MUST |
| SRS-AUTH-008 | Admin shall be able to create Guru/Dosen accounts. | MUST |
| SRS-AUTH-009 | Admin shall be able to manage permitted Mahasiswa account fields and active state. | MUST |
| SRS-AUTH-010 | Admin shall be able to issue a temporary password through a protected workflow. | MUST |
| SRS-AUTH-011 | Passwords shall be stored only as secure hashes using Laravel hashing facilities. | MUST |
| SRS-AUTH-012 | Public registration shall not allow a user to select Admin or Guru/Dosen role. | MUST |
| SRS-AUTH-013 | Deactivated accounts shall not be allowed to establish a new authenticated session. | MUST |
| SRS-AUTH-014 | The application shall provide a password-change workflow for authenticated users, and shall force a temporary password to be changed on first login. | MUST |

### 4.2 Personal tasks

| ID | Requirement | Priority |
|---|---|---|
| SRS-TASK-001 | Mahasiswa shall create a personal task. | MUST |
| SRS-TASK-002 | Mahasiswa shall view a paginated list of own personal tasks. | MUST |
| SRS-TASK-003 | Mahasiswa shall view a personal task detail. | MUST |
| SRS-TASK-004 | Mahasiswa shall update an owned personal task. | MUST |
| SRS-TASK-005 | Mahasiswa shall archive an owned personal task according to lifecycle rules. | MUST |
| SRS-TASK-006 | A personal task may have no course or one course context. | MUST |
| SRS-TASK-007 | Task priority shall be one of Rendah, Sedang, Tinggi. | MUST |
| SRS-TASK-008 | Task status shall be one of Belum Dikerjakan, Sedang Dikerjakan, Selesai. | MUST |
| SRS-TASK-009 | Completed tasks shall remain available in history. | MUST |
| SRS-TASK-010 | The system shall prevent one student from reading or modifying another student's personal task. | MUST |
| SRS-TASK-011 | The UI shall distinguish personal tasks from academic tasks. | MUST |
| SRS-TASK-012 | The system shall not expose ownership transfer unless separately approved. | MUST |

### 4.3 Academic tasks and progress

| ID | Requirement | Priority |
|---|---|---|
| SRS-ACAD-001 | Guru/Dosen shall create an academic task for a course they manage. | MUST |
| SRS-ACAD-002 | Academic task shall require a course. | MUST |
| SRS-ACAD-003 | Academic task definition shall be stored once, not duplicated per student. | MUST |
| SRS-ACAD-004 | At creation, the system shall create initial progress for every eligible enrolled student. | MUST |
| SRS-ACAD-005 | Initial progress shall be Belum Dikerjakan. | MUST |
| SRS-ACAD-006 | A student enrolling later shall receive progress for academic tasks that are not closed and not archived. | MUST |
| SRS-ACAD-007 | Distribution shall be idempotent. | MUST |
| SRS-ACAD-008 | Existing progress shall never be reset by re-distribution. | MUST |
| SRS-ACAD-009 | Progress shall be unique per academic task and student. | MUST |
| SRS-ACAD-010 | A student shall update only their own progress. | MUST |
| SRS-ACAD-011 | Guru/Dosen shall monitor progress for academic tasks in assigned courses. | MUST |
| SRS-ACAD-012 | Authorized Guru/Dosen may correct progress within assigned courses; actor identity shall be stored. | MUST |
| SRS-ACAD-013 | Admin monitoring shall be read-only. | MUST |
| SRS-ACAD-014 | Deadline shall not be used as the eligibility condition for late distribution. | MUST |
| SRS-ACAD-015 | Closing an academic task shall stop new progress distribution. | MUST |
| SRS-ACAD-016 | Leaving a course shall not delete existing progress history. | MUST |
| SRS-ACAD-017 | Progress updates shall not modify academic task definition fields. | MUST |
| SRS-ACAD-018 | Only the three approved task statuses shall be offered. | MUST |

### 4.4 Study activities

| ID | Requirement | Priority |
|---|---|---|
| SRS-STUDY-001 | Mahasiswa shall create a personal study activity. | MUST |
| SRS-STUDY-002 | Mahasiswa shall list and view own study activities. | MUST |
| SRS-STUDY-003 | Mahasiswa shall update an owned study activity. | MUST |
| SRS-STUDY-004 | Mahasiswa shall archive an owned study activity according to lifecycle rules. | MUST |
| SRS-STUDY-005 | A study activity may optionally reference a course. | MUST |
| SRS-STUDY-006 | Duration shall be a positive integer number of minutes. | MUST |
| SRS-STUDY-007 | Status shall be Direncanakan, Sedang Berlangsung, or Selesai. | MUST |
| SRS-STUDY-008 | Completed activities shall remain accessible in history. | MUST |
| SRS-STUDY-009 | Guru/Dosen and Admin shall not access the content of personal study activities. | MUST |

### 4.5 Courses and enrollment

| ID | Requirement | Priority |
|---|---|---|
| SRS-COURSE-001 | Each course shall reference exactly one Guru/Dosen. | MUST |
| SRS-COURSE-002 | One Guru/Dosen may be assigned to multiple courses. | MUST |
| SRS-COURSE-003 | Guru/Dosen shall manage only assigned courses. | MUST |
| SRS-COURSE-004 | Admin shall manage course data within administrative scope. | MUST |
| SRS-COURSE-005 | Mahasiswa shall view only courses in which they are enrolled. | MUST |
| SRS-ENR-001 | Admin shall create/manage student enrollment. | MUST |
| SRS-ENR-002 | Enrollment shall record academic year and semester. | MUST |
| SRS-ENR-003 | Enrollment uniqueness shall be student + course + academic year + semester. | MUST |
| SRS-ENR-004 | Only Terdaftar enrollment shall receive new academic task progress. | MUST |
| SRS-ENR-005 | Dibatalkan and Selesai enrollment records shall be retained. | MUST |
| SRS-ENR-006 | Re-enrollment in the same period shall reactivate the existing row; a different period creates a new row. | MUST |
| SRS-ENR-007 | Enrollment changes shall not delete existing academic progress. | MUST |

### 4.6 Notifications

| ID | Requirement | Priority |
|---|---|---|
| SRS-NOTIF-001 | The system shall persist notifications in MySQL. | MUST |
| SRS-NOTIF-002 | A user shall view only their own notifications. | MUST |
| SRS-NOTIF-003 | A user shall mark their own notification as read. | MUST |
| SRS-NOTIF-004 | Laravel Scheduler shall execute configured reminder jobs. | MUST |
| SRS-NOTIF-005 | Notification creation shall use a deduplication key scoped per recipient, so one event may notify many recipients without collision. | MUST |
| SRS-NOTIF-006 | Email and browser push shall not be required for MVP. | MUST |
| SRS-NOTIF-007 | Reminder lead times and preferences shall follow approved configuration. | TBD |

### 4.7 Archive and history

| ID | Requirement | Priority |
|---|---|---|
| SRS-ARCH-001 | Task, course, student, and study activity archive shall use soft archive where defined. | MUST |
| SRS-ARCH-002 | Archive shall preserve related progress and history. | MUST |
| SRS-ARCH-003 | Hard deletion shall be blocked where it would erase required history or violate restrictive FK rules. | MUST |
| SRS-ARCH-004 | Archived courses shall not receive new academic task distribution. | MUST |
| SRS-ARCH-005 | Archived students shall not receive new distribution. | MUST |

## 5. Non-functional requirements

### 5.1 Security
- `SRS-NFR-SEC-001`: all public deployment traffic shall use HTTPS.
- `SRS-NFR-SEC-002`: all state-changing browser forms shall be protected by CSRF tokens.
- `SRS-NFR-SEC-003`: passwords shall be hashed, never stored or logged in plaintext.
- `SRS-NFR-SEC-004`: authorization shall validate role, ownership, and resource relationship.
- `SRS-NFR-SEC-005`: session cookies shall use Secure, HttpOnly, and appropriate SameSite settings in production.
- `SRS-NFR-SEC-006`: secrets shall be stored in environment/secret configuration, not source control.
- `SRS-NFR-SEC-007`: database shall not be exposed publicly.
- `SRS-NFR-SEC-008`: login and sensitive account operations shall be rate-limited.
- `SRS-NFR-SEC-009`: logs shall not contain passwords, session IDs, or temporary credentials.

### 5.2 Usability and accessibility
- `SRS-NFR-UX-001`: interface shall be responsive for desktop, tablet, and mobile browsers.
- `SRS-NFR-UX-002`: core actions shall be keyboard accessible.
- `SRS-NFR-UX-003`: form errors shall be associated with relevant fields.
- `SRS-NFR-UX-004`: Light and Dark themes shall preserve equivalent information and functionality.
- `SRS-NFR-UX-005`: status shall not be conveyed by color alone.

### 5.3 Compatibility
- `SRS-NFR-COMP-001`: support current stable Chrome, Firefox, and Safari at release time.
- `SRS-NFR-COMP-002`: exact minimum browser versions shall be documented before release.
- `SRS-NFR-COMP-003`: avoid browser-specific features without fallback when they affect core functionality.

### 5.4 Reliability and integrity
- `SRS-NFR-REL-001`: multi-record business operations shall use database transactions.
- `SRS-NFR-REL-002`: unique constraints shall prevent duplicate enrollment, progress, and notification events.
- `SRS-NFR-REL-003`: application shall return a recoverable error when a concurrent update cannot safely be applied.
- `SRS-NFR-REL-004`: backup and restore procedures shall be documented and tested.
- `SRS-NFR-REL-005`: scheduler failures shall be observable.

### 5.5 Performance
Numerical response-time, throughput, dataset size, and availability targets are `TBD`. Establish a baseline measurement before setting numeric acceptance thresholds.

## 6. Data validation rules

- Username must be unique.
- NPM and NIP uniqueness follow their profile constraints.
- Password minimum length follows the approved security policy; current prior decision was at least 8 characters.
- Task title is required.
- Task deadline is required where current schema requires it.
- Priority must match master data.
- Task status must match master data.
- Academic task must have a course and creator lecturer.
- Personal task must have a Mahasiswa owner and may have a nullable course.
- Study duration must be greater than zero.
- Study activity owner must be Mahasiswa.
- Progress must reference an academic task and a valid student.
- Enrollment unique key includes academic year and semester.
- Date/time validation must use a single documented application timezone policy (`TBD`).

## 7. Concurrency requirements

Concurrency strategy shall be selected per entity and operation:
- Use database transactions for multi-step distribution, enrollment reactivation, archive, and account workflows.
- Use unique constraints for duplicate prevention.
- Use atomic updates for read/unread flags and counters.
- For user-editable records where lost updates are plausible, evaluate optimistic locking or compare-and-swap.
- Do not add a universal `row_version` to every table by default.
- Final entity-level matrix must be approved in `06_DATABASE_DESIGN.md`.

## 8. Error and response behavior

- Invalid input: return the form with field-level validation feedback.
- Unauthenticated request: redirect to login for browser routes.
- Unauthorized action: return 403 or a safe 404 according to resource-disclosure policy (`TBD`).
- Missing record: 404.
- Conflict: show a safe conflict message and preserve user-entered data where possible.
- Server error: show generic message; log diagnostic details server-side without secrets.
- Empty lists: show clear empty state and relevant allowed action.
- Scheduler failure: log and alert through operational monitoring mechanism (`TBD`).

## 9. Traceability

| PRD area | SRS requirements |
|---|---|
| Authentication/account | SRS-AUTH-* |
| Personal task | SRS-TASK-* |
| Academic task/progress | SRS-ACAD-* |
| Study activity | SRS-STUDY-* |
| Course/enrollment | SRS-COURSE-*, SRS-ENR-* |
| Notification | SRS-NOTIF-* |
| Archive/history | SRS-ARCH-* |
| Security | SRS-NFR-SEC-* |
| UX/browser | SRS-NFR-UX-*, SRS-NFR-COMP-* |

## 10. Open requirements

- Session lifetime and concurrent session policy.
- Temporary password expiry and mandatory change.
- Notification timing, preferences, timezone, and retention.
- Post-closure progress edit policy.
- Exact browser support floor.
- Performance and availability thresholds.
- 403 vs 404 resource disclosure policy.
- Final concurrency rules by table.
- Personal task deadline nullability (`PRD-TBD-024`, decided: required).

## 12. Keputusan 2026-11-18

| ID | Keputusan |
|---|---|
| REC-04 / PRD-TBD-022 | Enrollment Admin selalu `Terdaftar`; transisi diizinkan `Terdaftar`→`Dibatalkan` dan `Terdaftar`→`Selesai`; reaktivasi `Dibatalkan`/`Selesai`→`Terdaftar` melalui enrollment periode sama; `Ditolak` tidak ditawarkan di MVP. |
| REC-11 / PRD-TBD-023 | Admin hanya dapat mengubah `nama_lengkap`; `username`, `npm`, `nip_guru` immutable; status akun lewat aksi terpisah. |
| PRD-TBD-016 | Temporary password berlaku 72 jam, wajib diganti pada login pertama, route lain diblokir sampai diganti, dan penerbitan ulang mereset expiry. |

## 11. Rekonsiliasi dokumentasi (2026-11-18)

Rekonsiliasi disetujui pemilik proyek pada 2026-11-18. Seluruh butir REC-01 s.d. REC-15 berstatus `USER-DECIDED`; REC-04 dan REC-11 disetujui pada revisi 1.3.0 dengan aturan pada §12.

| ID | Requirement terdampak | Catatan |
|---|---|---|
| REC-01 | SRS-NOTIF-005 | Cakupan dedup adalah per penerima: unique `(id_user, dedup_key)` atau key yang memuat recipient. |
| REC-02 | SRS-TASK-*, SRS-STUDY-009 | Konvensi ownership data personal diseragamkan ke profil `mahasiswa`, bukan akun `users`. Menunggu keputusan CP2. |
| REC-04 | SRS-ENR-004/006 | Status awal `Terdaftar`; transisi `Dibatalkan`/`Selesai` dari `Terdaftar`; reaktivasi periode sama; `Ditolak` tidak dipakai. |
| REC-11 | SRS-AUTH-009 | Admin hanya mengubah `nama_lengkap`; identifier tetap immutable. |
| REC-07 | — | Tambahan route create/edit aktivitas belajar (celah UI `ST-08`). |
| REC-08 | SRS-ACAD-011 | Tambahan route rekap progres per mata kuliah (celah UI `GD-07`). |
| REC-09 | SRS-AUTH-010 | Temporary password juga mencakup akun Guru/Dosen. |
| REC-12 | SRS-AUTH-007, §8 | Usulan: 404 untuk resource milik pengguna lain, 403 untuk penolakan tingkat role. |
| REC-13 | §6 | Usulan: simpan timestamp UTC, tampilkan dengan timezone aplikasi (`Asia/Jakarta`). |
| REC-14 | SRS-TASK-* | Usulan: `deadline` wajib untuk tugas personal dan akademik sampai diputuskan lain. |
