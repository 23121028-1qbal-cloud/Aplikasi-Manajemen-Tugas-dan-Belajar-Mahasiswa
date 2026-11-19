# 08 — ROUTE AND API SPECIFICATION

| Metadata | Value |
|---|---|
| Document ID | DOC-08 |
| Version | 1.7.0 |
| Status | Draft |
| Primary interface | Laravel Web Routes |
| JSON API | Only when a specific UI/integration need exists |

## 1. Purpose

The previous mobile-oriented REST API contract is not the primary contract for the web-only application. Laravel Blade submits ordinary HTTP forms to `routes/web.php`. This document defines route groups and operation semantics, not a requirement to implement every legacy API endpoint.

## 2. General conventions

- Use named Laravel routes.
- Use HTTP GET for read-only pages.
- Use POST for create and explicit actions.
- Use PUT/PATCH for updates where appropriate.
- Use DELETE only for approved deletion semantics; archive is preferred where specified.
- All state-changing browser requests require CSRF protection.
- Authenticated routes use session middleware.
- Validate with Form Requests or equivalent server-side validation.
- Authorize with policies/gates and resource scoping.
- Redirect after successful form submission (Post/Redirect/Get).
- Return validation errors to the originating form.
- JSON responses are allowed only for explicitly needed asynchronous interactions.

## 3. Route groups

### 3.1 Guest routes
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/login` | `login` | Show login |
| POST | `/login` | `login.store` | Authenticate |
| GET | `/register` | `register` | Show student registration |
| POST | `/register` | `register.store` | Create Mahasiswa account/profile |

### 3.2 Authenticated shared routes
| Method | URI | Name | Action |
|---|---|---|---|
| POST | `/logout` | `logout` | Invalidate session |
| GET | `/account` | `account.show` | Own account |
| GET | `/account/password` | `account.password.edit` | Change password form |
| PUT | `/account/password` | `account.password.update` | Change password |
| GET | `/notifications` | `notifications.index` | Own notifications |
| PATCH | `/notifications/{notification}/read` | `notifications.read` | Mark own notification read |
| PATCH | `/notifications/read-all` | `notifications.read-all` | Mark own notifications read |
| GET | `/account/appearance` | `account.appearance.edit` | Appearance settings page (REC-10, `USER-DECIDED`) |
| PATCH | `/account/appearance` | `account.appearance.update` | Persist Light/Dark preference (REC-10, `USER-DECIDED`) |

### 3.3 Mahasiswa routes
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/mahasiswa/dashboard` | `mahasiswa.dashboard` | Dashboard |
| GET | `/mahasiswa/tasks` | `mahasiswa.tasks.index` | Own tasks + assigned academic tasks |
| GET | `/mahasiswa/tasks/personal/create` | `mahasiswa.tasks.personal.create` | Create form |
| POST | `/mahasiswa/tasks/personal` | `mahasiswa.tasks.personal.store` | Create personal task |
| GET | `/mahasiswa/tasks/personal/{task}` | `mahasiswa.tasks.personal.show` | Detail |
| GET | `/mahasiswa/tasks/personal/{task}/edit` | `mahasiswa.tasks.personal.edit` | Edit form |
| PUT | `/mahasiswa/tasks/personal/{task}` | `mahasiswa.tasks.personal.update` | Update |
| PATCH | `/mahasiswa/tasks/personal/{task}/archive` | `mahasiswa.tasks.personal.archive` | Archive |
| GET | `/mahasiswa/academic-tasks/{task}` | `mahasiswa.academic-tasks.show` | Academic detail |
| PATCH | `/mahasiswa/academic-tasks/{task}/progress` | `mahasiswa.academic-tasks.progress.update` | Update own progress |
| GET | `/mahasiswa/task-history` | `mahasiswa.task-history` | Completed/archived history |
| GET | `/mahasiswa/study-activities` | `mahasiswa.study-activities.index` | List |
| GET | `/mahasiswa/study-history` | `mahasiswa.study-history` | Completed/archived study history (REC-07, `USER-DECIDED`) |
| GET | `/mahasiswa/study-activities/create` | `mahasiswa.study-activities.create` | Create form (REC-07, `USER-DECIDED`) |
| GET | `/mahasiswa/study-activities/{activity}/edit` | `mahasiswa.study-activities.edit` | Edit form (REC-07, `USER-DECIDED`) |
| POST | `/mahasiswa/study-activities` | `mahasiswa.study-activities.store` | Create |
| GET | `/mahasiswa/study-activities/{activity}` | `mahasiswa.study-activities.show` | Detail |
| PUT | `/mahasiswa/study-activities/{activity}` | `mahasiswa.study-activities.update` | Update |
| PATCH | `/mahasiswa/study-activities/{activity}/archive` | `mahasiswa.study-activities.archive` | Archive |
| GET | `/mahasiswa/courses` | `mahasiswa.courses.index` | Enrolled courses |
| GET | `/mahasiswa/courses/{course}` | `mahasiswa.courses.show` | Course detail |

### 3.4 Guru/Dosen routes
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/guru-dosen/dashboard` | `lecturer.dashboard` | Dashboard |
| GET | `/guru-dosen/courses` | `lecturer.courses.index` | Assigned courses |
| GET | `/guru-dosen/courses/{course}` | `lecturer.courses.show` | Course detail |
| GET | `/guru-dosen/courses/{course}/progress` | `lecturer.courses.progress` | Course progress overview (`GD-07`, REC-08, `USER-DECIDED`) |
| PUT | `/guru-dosen/courses/{course}` | `lecturer.courses.update` | Update allowed course fields |
| GET | `/guru-dosen/academic-tasks` | `lecturer.academic-tasks.index` | Task list |
| GET | `/guru-dosen/academic-tasks/create` | `lecturer.academic-tasks.create` | Create form |
| POST | `/guru-dosen/academic-tasks` | `lecturer.academic-tasks.store` | Create + distribute |
| GET | `/guru-dosen/academic-tasks/{task}` | `lecturer.academic-tasks.show` | Detail |
| GET | `/guru-dosen/academic-tasks/{task}/edit` | `lecturer.academic-tasks.edit` | Edit form |
| PUT | `/guru-dosen/academic-tasks/{task}` | `lecturer.academic-tasks.update` | Update definition |
| PATCH | `/guru-dosen/academic-tasks/{task}/close` | `lecturer.academic-tasks.close` | Close task |
| GET | `/guru-dosen/academic-tasks/{task}/progress` | `lecturer.academic-tasks.progress` | Per-student progress |
| PATCH | `/guru-dosen/progress/{progress}` | `lecturer.progress.update` | Correct progress within assigned course |

Course create and lecturer assignment permissions must follow the final approved course governance matrix. Do not infer that a lecturer may reassign themselves or another lecturer without explicit permission.

### 3.5 Admin routes
| Method | URI | Name | Action |
|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | Admin dashboard |
| GET | `/admin/students` | `admin.students.index` | Student list |
| GET | `/admin/students/{student}` | `admin.students.show` | Student detail |
| PUT | `/admin/students/{student}` | `admin.students.update` | Update allowed fields |
| PATCH | `/admin/students/{student}/status` | `admin.students.status` | Activate/deactivate |
| PATCH | `/admin/students/{student}/archive` | `admin.students.archive` | Soft-archive profile + deactivate account |
| PATCH | `/admin/students/{student}/restore` | `admin.students.restore` | Restore profile + reactivate account |
| POST | `/admin/students/{student}/temporary-password` | `admin.students.temporary-password` | Issue temporary password |
| GET | `/admin/lecturers` | `admin.lecturers.index` | Lecturer list |
| POST | `/admin/lecturers` | `admin.lecturers.store` | Create lecturer account/profile |
| GET | `/admin/lecturers/{lecturer}` | `admin.lecturers.show` | Lecturer detail |
| PUT | `/admin/lecturers/{lecturer}` | `admin.lecturers.update` | Update allowed fields |
| PATCH | `/admin/lecturers/{lecturer}/archive` | `admin.lecturers.archive` | Soft-archive profile + deactivate account; blocked if assigned to a non-archived course |
| PATCH | `/admin/lecturers/{lecturer}/restore` | `admin.lecturers.restore` | Restore profile + reactivate account |
| POST | `/admin/lecturers/{lecturer}/temporary-password` | `admin.lecturers.temporary-password` | Issue temporary password (REC-09) |
| GET | `/admin/courses` | `admin.courses.index` | Course list |
| POST | `/admin/courses` | `admin.courses.store` | Create course |
| PUT | `/admin/courses/{course}` | `admin.courses.update` | Update course |
| PATCH | `/admin/courses/{course}/archive` | `admin.courses.archive` | Archive course |
| GET | `/admin/enrollments` | `admin.enrollments.index` | Enrollment list |
| POST | `/admin/enrollments` | `admin.enrollments.store` | Create/reactivate enrollment |
| PUT | `/admin/enrollments/{enrollment}` | `admin.enrollments.update` | Update permitted enrollment fields |
| GET | `/admin/academic-monitoring` | `admin.academic-monitoring.index` | Read-only academic monitoring |
| GET | `/admin/academic-monitoring/tasks/{task}` | `admin.academic-monitoring.tasks.show` | Read-only task/progress |

## 4. Route security requirements

- All role-specific groups require authentication and role middleware.
- Policies must verify resource ownership and relationship.
- Student progress route must resolve progress by authenticated student, not trust submitted student ID.
- Student progress mutation must be rejected when the task is closed or archived; lecturer correction is allowed after close but not after archive.
- Lecturer progress correction must verify the task course belongs to the lecturer.
- Admin monitoring must use dedicated read-only queries/resources.
- Admin monitoring must never join/select personal task descriptions or study activity content.
- Notification routes must scope by authenticated recipient.
- Sensitive operations must be throttled and logged safely.

## 5. Academic distribution semantics

Create academic task:
1. Validate lecturer and course relationship.
2. Begin transaction.
3. Insert one academic task definition.
4. Select `Terdaftar` enrollments for the course.
5. Insert missing progress rows with `Belum Dikerjakan`.
6. Do not overwrite existing progress.
7. Create recipient notifications using unique dedup keys.
8. Commit transaction.

Late enrollment:
1. Enrollment becomes `Terdaftar`.
2. Find open, non-archived academic tasks for course.
3. Insert missing progress rows only.
4. Preserve existing progress.
5. Create one assignment notification per newly created progress, deduplicated; do not notify for progress that already existed.
6. Commit transaction.

## 6. Error behavior

| Condition | Browser behavior |
|---|---|
| Unauthenticated | Redirect to login |
| Validation error | Redirect back with field errors and old safe input |
| Forbidden | 403 or safe 404 per disclosure policy |
| Missing resource | 404 |
| Duplicate enrollment/progress | Resolve idempotently or show conflict; never duplicate |
| Stale concurrent update | Conflict feedback; preserve safe form data |
| Student updates progress after task closure | Denied with safe message; progress remains unchanged |
| Progress update on archived task | Denied for all roles |
| Archive lecturer with a non-archived course | Validation error; lecturer remains active |
| Restore archived account | Account active again; no backfill of missed progress |
| Unexpected error | Generic message; server-side diagnostic log |

## 7. JSON endpoints

No standalone public API is required for MVP. If an asynchronous UI feature requires JSON, add only the minimal endpoint and document:
- consumer and reason;
- route/method;
- authentication and CSRF/session expectations;
- request/response schema;
- validation;
- authorization;
- error behavior;
- tests.

Do not automatically port all legacy mobile API endpoints.

## 8. Pagination and filtering

Paginate large account, course, enrollment, task, and progress listings. Exact page size is an implementation/configuration decision. Search/filter parameters must be validated and query-scoped.

## 9. Open API decisions

- Exact form field names and validation lengths.
- Pagination defaults and sort rules.
- Whether any async JSON route is actually required.

Sudah ditutup: kewenangan course (Admin membuat/menugaskan; dosen hanya mengubah nama), 403/404 (REC-12), temporary password reveal/expiry (`PRD-TBD-016`), route arsip/restore (`PRD-TBD-025`), dan route preferensi notifikasi ditunda karena preferensi per pengguna tidak termasuk MVP (`PRD-TBD-018`).

## 10. Rekonsiliasi route (2026-11-18)

Audit UI/UX terhadap dokumen route menemukan halaman tanpa route dan route tanpa aturan. Penambahan REC-07, REC-08, REC-09, dan REC-10 disetujui pemilik proyek pada 2026-11-18 (`USER-DECIDED`). REC-11 disetujui pada 1.3.0: `PUT /admin/students/{student}` dan `PUT /admin/lecturers/{lecturer}` hanya menerima `nama_lengkap`.

| ID | Celah | Usulan |
|---|---|---|
| REC-07 | `ST-08` Create/Edit aktivitas belajar tidak memiliki route GET | Tambahkan `GET .../create` dan `GET .../{activity}/edit` |
| REC-08 | `GD-07` Course Progress Overview tidak memiliki route | Tambahkan `GET /guru-dosen/courses/{course}/progress` |
| REC-09 | Reset password Guru/Dosen tidak memiliki route | Tambahkan `POST /admin/lecturers/{lecturer}/temporary-password` |
| REC-10 | `SH-04` Appearance Settings tidak memiliki route; persistensi tema `TBD` | Route GET/PATCH `/account/appearance`; persistensi awal memakai cookie + atribut `data-theme` tanpa mengubah schema |
| REC-11 | Field akun yang boleh diubah Admin belum ditetapkan | Hanya `nama_lengkap`; `role`, `password`, `username`, `npm`, `nip_guru`, dan `id` tidak diterima |

Aturan keamanan tidak berubah: seluruh route di atas tetap memerlukan session, CSRF pada mutasi, **Policy/Gate resource** (`ARCH-011`), dan scoping query di server.

Status route:

| Route | Status |
|---|---|
| `PUT /admin/students/{student}` (`admin.students.update`) | Diimplementasikan; body hanya `nama_lengkap` |
| `PUT /admin/lecturers/{lecturer}` (`admin.lecturers.update`) | Diimplementasikan; body hanya `nama_lengkap` |
| `PUT /admin/enrollments/{enrollment}` (`admin.enrollments.update`) | Diimplementasikan; body hanya `status` dengan guard transisi REC-04 |
| `PUT /guru-dosen/courses/{course}` (`lecturer.courses.update`) | Diimplementasikan; body hanya `nama_mata_kuliah` + `row_version`. Kode dan dosen pengampu tetap kewenangan Admin |
| `POST /guru-dosen/academic-tasks` (`lecturer.academic-tasks.store`) | Diimplementasikan; membuat definisi dan progres awal dalam satu transaksi |
| `PUT /guru-dosen/academic-tasks/{task}` (`lecturer.academic-tasks.update`) | Diimplementasikan; hanya definisi, memakai `row_version` |
| `PATCH /guru-dosen/academic-tasks/{task}/close` (`lecturer.academic-tasks.close`) | Diimplementasikan; idempotent dan menghentikan distribusi baru |
| `PATCH /mahasiswa/academic-tasks/{task}/progress` (`mahasiswa.academic-tasks.progress.update`) | Diimplementasikan; progres sendiri + `row_version` |
| `PATCH /guru-dosen/progress/{progress}` (`lecturer.progress.update`) | Diimplementasikan; koreksi dalam mata kuliah yang diampu + `row_version`, aktor dicatat |
| `GET /admin/academic-monitoring` dan `/tasks/{task}` | Diimplementasikan read-only; tidak menyentuh tugas personal/aktivitas belajar |
| `GET/PATCH /account/appearance` (`account.appearance.*`) | Diimplementasikan; preferensi Light/Dark disimpan pada cookie `theme`, tanpa schema tambahan |
| `GET /guru-dosen/courses/{course}/progress` (`lecturer.courses.progress`) | Diimplementasikan; rekap per tugas dalam mata kuliah yang diampu (REC-08/GD-07) |
| `GET /notifications`, `PATCH /notifications/{notification}/read`, `PATCH /notifications/read-all` | Diimplementasikan; scoped ke penerima, read/read-all idempotent, dan badge unread di header |
| `PATCH /admin/students/{student}/archive` dan `/restore` | Diimplementasikan; arsip menonaktifkan akun, restore mengaktifkan kembali tanpa backfill progres |
| `PATCH /admin/lecturers/{lecturer}/archive` dan `/restore` | Diimplementasikan; dosen harus bebas mata kuliah non-arsip |
