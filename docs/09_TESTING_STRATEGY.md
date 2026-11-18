# 09 — TESTING STRATEGY

| Metadata | Value |
|---|---|
| Document ID | DOC-09 |
| Version | 1.2.0 |
| Status | Draft |
| Target | Laravel Web Application |

## 1. Purpose

Testing validates product behavior, authorization, data integrity, responsive UX, and deployment readiness. Tests must trace to SRS requirements and must not assume a UI-hidden action is secure.

## 2. Test levels

### Unit tests
- Domain/service calculations and state transitions.
- Eligibility rules for task distribution.
- Notification dedup key generation.
- Validation helpers where isolated behavior exists.

### Feature/integration tests
- HTTP routes, middleware, sessions, validation.
- Database transactions, unique constraints, policies.
- Task distribution and late enrollment.
- Archive behavior and history preservation.
- Notification ownership and read state.

### Browser/E2E tests
- Registration and login.
- Role-based navigation.
- Personal task workflows.
- Academic task creation/distribution/progress.
- Study activity workflow.
- Admin account/enrollment/monitoring.
- Light/Dark and responsive navigation.
- Browser compatibility.

### Operational tests
- Scheduler execution.
- Backup creation and restore.
- HTTPS and security headers.
- Database not publicly exposed.
- Deployment rollback procedure.

## 3. Test environment

- Dedicated test database, never production data.
- Deterministic seeders/factories.
- Isolated user accounts for all three roles.
- Browser automation against staging or local environment.
- Production secrets must not be copied into test fixtures.
- Test time/timezone should be controlled for scheduled reminders.

## 4. Core test matrix

| Test ID | Scenario | Expected result | SRS |
|---|---|---|---|
| T-AUTH-01 | Mahasiswa registers valid data | Student account/profile created | AUTH-001/002 |
| T-AUTH-02 | Public registration submits Admin role | Rejected; role remains Mahasiswa | AUTH-012 |
| T-AUTH-03 | Valid username/password login | Session established and regenerated | AUTH-003/004 |
| T-AUTH-04 | Invalid credentials | Generic authentication error | AUTH-003 |
| T-AUTH-05 | Logout then revisit protected page | Session no longer grants access | AUTH-005/006 |
| T-AUTH-06 | Inactive account login | Denied | AUTH-013 |
| T-AUTH-07 | Admin issues temporary password | Protected operation succeeds and is audited safely | AUTH-010 |
| T-TASK-01 | Student creates personal task | Own task saved | TASK-001 |
| T-TASK-02 | Student accesses another student's task | Denied/not disclosed | TASK-010 |
| T-TASK-03 | Personal task without course | Accepted | TASK-006 |
| T-TASK-04 | Personal task with invalid priority/status | Validation fails | TASK-007/008 |
| T-TASK-05 | Completed task appears in history | Record retained | TASK-009 |
| T-ACAD-01 | Lecturer creates task in assigned course | Definition stored | ACAD-001/002 |
| T-ACAD-02 | Lecturer creates task in unassigned course | Denied | ACAD-001 |
| T-ACAD-03 | New academic task with N enrolled students | One initial progress per eligible student | ACAD-004/005 |
| T-ACAD-04 | Distribution invoked twice | No duplicate progress | ACAD-007/009 |
| T-ACAD-05 | Existing progress is re-distributed | Status unchanged | ACAD-008 |
| T-ACAD-06 | Student enrolls after task creation; task open | Missing progress created | ACAD-006 |
| T-ACAD-07 | Student enrolls after task closed | No progress created | ACAD-015 |
| T-ACAD-08 | Deadline passed but task open | Eligibility follows closure, not deadline | ACAD-014 |
| T-ACAD-09 | Student updates own progress | Status updated | ACAD-010 |
| T-ACAD-10 | Student updates another student's progress | Denied | ACAD-010 |
| T-ACAD-11 | Lecturer corrects progress in own course | Update and actor recorded | ACAD-012 |
| T-ACAD-12 | Admin attempts progress mutation | Denied | ACAD-013 |
| T-STUDY-01 | Student creates activity with positive duration | Saved | STUDY-001/006 |
| T-STUDY-02 | Duration zero or negative | Rejected | STUDY-006 |
| T-STUDY-03 | Lecturer/Admin attempts to view private activity | Denied | STUDY-009 |
| T-ENR-01 | Duplicate same-period enrollment | No duplicate row | ENR-003 |
| T-ENR-02 | Same-period reactivation | Existing row reactivated | ENR-006 |
| T-ENR-03 | Different academic period | New row accepted | ENR-006 |
| T-ENR-04 | Enrollment cancelled | Existing progress retained | ENR-007 |
| T-NOTIF-01 | User opens own notification list | Own notifications only | NOTIF-002 |
| T-NOTIF-02 | User marks another user's notification read | Denied | NOTIF-002/003 |
| T-NOTIF-03 | Same event processed twice | One notification per recipient | NOTIF-005 |
| T-ARCH-01 | Course archived | New distribution blocked; history retained | ARCH-002/004 |
| T-ARCH-02 | Student archived | New distribution blocked; history retained | ARCH-002/005 |
| T-ARCH-03 | Hard delete parent with history | Restricted | ARCH-003 |

## 5. Authorization test matrix

Every protected resource must test:
- unauthenticated access;
- correct role and correct ownership;
- correct role but wrong ownership;
- wrong role;
- correct role but wrong course relationship;
- archived/closed resource conditions;
- direct URL access bypassing navigation;
- forged IDs in form payload.

## 6. Concurrency and race-condition tests

Must test:
1. Two concurrent requests creating same enrollment.
2. Two concurrent academic distribution jobs.
3. Student enrollment concurrent with academic task creation.
4. Student progress update concurrent with lecturer correction.
5. Two edits to same task definition.
6. Concurrent notification generation for same event.
7. Archive concurrent with update/distribution.

Expected outcomes:
- unique constraints prevent duplicate rows;
- transaction boundaries preserve invariants;
- existing progress is not reset;
- conflicts are reported rather than silently lost;
- no history is deleted.

## 7. UI and browser tests

Test modern stable Chrome, Firefox, Safari at release time. At minimum:
- desktop width;
- tablet width;
- narrow mobile width;
- sidebar/offcanvas navigation;
- Light and Dark theme;
- form errors;
- empty/loading/error states;
- keyboard navigation and focus visibility;
- responsive tables/lists.

Exact browser versions and viewport matrix are finalized before release.

## 8. Security tests

- CSRF on state-changing forms.
- Session fixation checks.
- Secure cookie flags in production.
- Password hashing.
- Rate limiting login and sensitive routes.
- XSS output escaping.
- SQL injection protections through query binding/ORM.
- IDOR/resource ownership.
- Mass assignment protection.
- Secrets absent from repository/logs.
- MySQL not publicly accessible.
- HTTPS enforced.
- Temporary password not logged or exposed after issuance.

## 9. Performance tests

Establish baseline and then agree numeric thresholds. Measure:
- dashboard queries;
- task list pagination;
- course progress report;
- distribution batch size;
- notification list;
- scheduler processing time;
- MySQL query plans and indexes.

Do not claim a performance SLA until thresholds are approved and tested.

## 10. Test data and fixtures

Use factories for:
- Mahasiswa with unique NPM and username;
- Guru/Dosen with unique NIP and username;
- Admin;
- courses assigned to lecturers;
- enrollments across periods and statuses;
- personal and academic tasks;
- progress rows;
- notifications.

Seed only reference values and safe test accounts. Never seed a production password.

## 11. Definition of Done

A feature is complete only when:
- relevant SRS requirements are implemented;
- positive and negative tests pass;
- authorization tests pass;
- database invariants are verified;
- responsive UI is reviewed;
- errors and empty states are handled;
- documentation and traceability are updated;
- no unresolved critical security defect remains.

## 12. Open testing decisions

- E2E framework.
- Numeric performance targets.
- Exact browser versions.
- Accessibility audit tooling.
- Backup restore frequency.
- Production smoke test and rollback owner.

## 13. Rekonsiliasi pengujian (2026-11-18)

| ID | Dampak pada test | Test tambahan (disetujui) |
|---|---|---|
| REC-01 | `T-NOTIF-03` harus membuktikan satu event membuat notifikasi untuk **semua** penerima tanpa tabrakan unique | `T-NOTIF-04`: distribusi ke 3 mahasiswa menghasilkan 3 notifikasi, dan pemrosesan ulang tidak menambah baris |
| REC-02 | Scoping ownership berubah bila `aktivitas_belajar` memakai `id_mahasiswa` | `T-STUDY-04`: dosen/admin tidak dapat mengakses aktivitas via ID langsung; mahasiswa lain mendapat 404 |
| REC-04 | Transisi status enrollment belum ada | `T-ENR-05`: transisi tidak sah ditolak; `Terdaftar` tetap satu baris per periode |
| REC-10 | Persistensi tema | Test layout: `data-theme` mengikuti cookie dan bertahan antar request |
| REC-12 | Kebijakan disclosure | Test memastikan resource milik pengguna lain mengembalikan 404, bukan 403 yang membocorkan keberadaan |
| REC-15 | Versi runtime | Catat versi aktual pada laporan test; jangan klaim kompatibilitas MySQL sebelum diuji |

Test baseline Phase 2 mencakup boot aplikasi, render layout guest/authenticated, dan build aset. Test domain dijalankan bertahap sesuai fase; Phase 3 mencakup registrasi, login/logout, pembatasan role, bootstrap admin, provisioning dosen, dan temporary password.
