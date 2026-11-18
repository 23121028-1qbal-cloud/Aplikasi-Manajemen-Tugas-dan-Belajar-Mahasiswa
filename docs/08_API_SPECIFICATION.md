# 08 — ROUTE AND API SPECIFICATION

| Metadata | Value |
|---|---|
| Document ID | DOC-08 |
| Version | 1.2.0 |
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
| GET | `/account/appearance` | `account.appearance.edit` | **PROPOSED** (REC-10): appearance settings page |
| PATCH | `/account/appearance` | `account.appearance.update` | **PROPOSED** (REC-10): persist Light/Dark preference |

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
| GET | `/mahasiswa/study-activities/create` | `mahasiswa.study-activities.create` | **PROPOSED** (REC-07): create form |
| GET | `/mahasiswa/study-activities/{activity}/edit` | `mahasiswa.study-activities.edit` | **PROPOSED** (REC-07): edit form |
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
| GET | `/guru-dosen/courses/{course}/progress` | `lecturer.courses.progress` | **PROPOSED** (REC-08): course progress overview (`GD-07`) |
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
| POST | `/admin/students/{student}/temporary-password` | `admin.students.temporary-password` | Issue temporary password |
| GET | `/admin/lecturers` | `admin.lecturers.index` | Lecturer list |
| POST | `/admin/lecturers` | `admin.lecturers.store` | Create lecturer account/profile |
| GET | `/admin/lecturers/{lecturer}` | `admin.lecturers.show` | Lecturer detail |
| PUT | `/admin/lecturers/{lecturer}` | `admin.lecturers.update` | Update allowed fields |
| POST | `/admin/lecturers/{lecturer}/temporary-password` | `admin.lecturers.temporary-password` | **PROPOSED** (REC-09): issue temporary password |
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
5. Commit transaction.

## 6. Error behavior

| Condition | Browser behavior |
|---|---|
| Unauthenticated | Redirect to login |
| Validation error | Redirect back with field errors and old safe input |
| Forbidden | 403 or safe 404 per disclosure policy |
| Missing resource | 404 |
| Duplicate enrollment/progress | Resolve idempotently or show conflict; never duplicate |
| Stale concurrent update | Conflict feedback; preserve safe form data |
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

- Final course create/assignment authority.
- 403 versus 404 policy for inaccessible records.
- Exact form field names and validation lengths.
- Pagination defaults and sort rules.
- Whether any async JSON route is actually required.
- Temporary password reveal/expiry workflow.

## 10. Rekonsiliasi route (2026-11-18)

Audit UI/UX terhadap dokumen route menemukan halaman tanpa route dan route tanpa aturan. Penambahan REC-07, REC-08, REC-09, dan REC-10 disetujui pemilik proyek pada 2026-11-18 (`USER-DECIDED`). REC-11 tetap `OPEN` karena daftar field final belum tersedia.

| ID | Celah | Usulan |
|---|---|---|
| REC-07 | `ST-08` Create/Edit aktivitas belajar tidak memiliki route GET | Tambahkan `GET .../create` dan `GET .../{activity}/edit` |
| REC-08 | `GD-07` Course Progress Overview tidak memiliki route | Tambahkan `GET /guru-dosen/courses/{course}/progress` |
| REC-09 | Reset password Guru/Dosen tidak memiliki route | Tambahkan `POST /admin/lecturers/{lecturer}/temporary-password` |
| REC-10 | `SH-04` Appearance Settings tidak memiliki route; persistensi tema `TBD` | Route GET/PATCH `/account/appearance`; persistensi awal memakai cookie + atribut `data-theme` tanpa mengubah schema |
| REC-11 | Field akun yang boleh diubah Admin belum ditetapkan | Tunggu `PRD-TBD-023`; validasi Form Request tidak boleh menerima `role`, `password`, atau `id` |

Aturan keamanan tidak berubah: seluruh route di atas tetap memerlukan session, CSRF pada mutasi, policy resource, dan scoping query di server.
