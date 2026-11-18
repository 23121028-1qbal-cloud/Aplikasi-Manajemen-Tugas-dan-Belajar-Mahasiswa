# 05 — COMPONENT LIBRARY

| Metadata | Value |
|---|---|
| Document ID | DOC-05 |
| Version | 1.1.0 |
| Status | Draft |
| UI stack | Blade + Bootstrap |

## 1. Purpose and rules

Dokumen ini mendefinisikan komponen UI reusable. Implementasi menggunakan Blade components/partials dan Bootstrap. Komponen hanya bertanggung jawab pada presentasi; authorization dan business rules berada di backend.

## 2. Component architecture

Proposed structure:

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── guest.blade.php
│   └── partials/
│       ├── sidebar.blade.php
│       ├── header.blade.php
│       └── footer.blade.php
├── components/
│   ├── buttons/
│   ├── forms/
│   ├── feedback/
│   ├── navigation/
│   ├── data-display/
│   └── overlays/
└── pages/
    ├── auth/
    ├── mahasiswa/
    ├── guru-dosen/
    └── admin/
```

## 3. Layout components

| Component | Responsibility | Variants/states |
|---|---|---|
| App Shell | Authenticated page frame | Desktop, mobile |
| Guest Layout | Login/registration frame | Login, register |
| Sidebar | Role-aware navigation | Expanded, compact |
| Mobile Navigation | Compact navigation | Offcanvas/menu |
| Header | Page title, notification, profile | Default, compact |
| Breadcrumb | Navigation context | 1+ levels |
| Content Container | Page width and spacing | Standard, wide |

Navigation items must be filtered by role, but route policies remain the security boundary.

## 4. Navigation components

- `NavItem`: label, icon, route, active state.
- `SidebarGroup`: groups related menu items.
- `MobileMenuButton`: opens mobile navigation.
- `Breadcrumbs`: current location.
- `UserMenu`: profile and logout.
- `NotificationBell`: unread count for current user only.

All links must be ordinary links for navigation. Mutating operations must use protected POST/PATCH/DELETE form submissions.

## 5. Form components

| Component | Required behavior |
|---|---|
| Text Input | Label, hint, error, required state |
| Textarea | Label, character guidance if limit exists |
| Password Input | Masked input; never echo submitted password |
| Select | Explicit label and selected value |
| Date/Time Input | Consistent timezone and server validation |
| Checkbox/Switch | Accessible label and state |
| Form Group | Label + control + hint + error |
| Error Summary | Summary of validation errors |
| Submit Button | Loading/disabled state |
| Search Input | Query and clear affordance |

Do not invent max lengths or mandatory fields where database/product decisions are still open.

## 6. Data display components

| Component | Purpose |
|---|---|
| Page Header | Page title and primary action |
| Summary Card | Approved aggregate only |
| Data Table | Admin/lecturer listings |
| Responsive List | Mobile equivalent for table |
| Task Card | Task title, type, deadline, priority/status |
| Study Activity Card | Activity title, course, time, duration, status |
| Course Card | Course code/name/lecturer |
| Progress Table | Per-student academic progress |
| Status Badge | Text + semantic style |
| Priority Badge | Rendah/Sedang/Tinggi |
| Task Type Badge | Personal/Academic |
| Empty State | No data + allowed action |
| Pagination | Server-side page navigation |
| Filter Bar | Applicable filters only |

Do not show personal task or study activity content in Admin monitoring components.

## 7. Feedback components

- Alert: success, info, warning, danger.
- Toast: short-lived operation feedback; must not be sole place for critical errors.
- Inline validation message.
- Loading indicator/skeleton.
- Empty state.
- Error state with retry only when retry is safe.
- Conflict message for stale updates.

## 8. Overlay components

- Confirmation modal for archive, close academic task, and other consequential operations.
- Offcanvas for mobile navigation.
- Dropdown for account and contextual actions.
- Modal must trap/restore focus and be keyboard accessible.

## 9. Role-specific component rules

### Mahasiswa
- Personal task actions only on owned personal task.
- Academic task definition is read-only.
- Progress editor applies only to current student's progress.
- Study activity controls apply only to own records.

### Guru/Dosen
- Course controls only for assigned courses.
- Academic task create/edit/close controls only within assigned course.
- Progress table limited to assigned courses.

### Admin
- Account and enrollment controls follow administrative policy.
- Academic monitoring components are read-only.
- No personal content component may be mounted in Admin screens.

## 10. Component acceptance checklist

- [ ] Responsive on mobile and desktop.
- [ ] Keyboard accessible.
- [ ] Visible focus state.
- [ ] Correct Light/Dark appearance.
- [ ] Loading, empty, error, disabled states where applicable.
- [ ] Text labels accompany semantic colors.
- [ ] Escaped output by default.
- [ ] Mutations use CSRF-protected forms.
- [ ] Authorization tested independently of UI visibility.
- [ ] No sensitive data exposed in HTML or client-side state.

## 11. Open decisions

- Exact icon library.
- Final Bootstrap version.
- Exact design tokens.
- Toast library/native Bootstrap usage.
- Whether calendar component is MVP or later.
