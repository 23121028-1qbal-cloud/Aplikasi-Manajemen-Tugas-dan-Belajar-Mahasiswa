# 04 — DESIGN SYSTEM

| Metadata | Value |
|---|---|
| Document ID | DOC-04 |
| Version | 1.2.0 |
| Status | Draft / values mostly Proposed |
| Platform | Web |
| CSS framework | Bootstrap |

## 1. Purpose

Menetapkan aturan visual konsisten untuk seluruh halaman web. Design System tidak menentukan business rules atau hak akses; rujukan tetap PRD dan SRS.

## 2. Design principles

- Modern, minimal, academic productivity.
- Hierarki informasi jelas.
- Netral dan tidak ramai.
- Responsive.
- Accessible.
- Komponen konsisten pada seluruh role.
- Light dan Dark theme memiliki makna dan kontras yang setara.

## 3. Technology alignment

- Blade templates sebagai markup server-rendered.
- Bootstrap sebagai CSS framework.
- Gunakan Bootstrap utilities dan components sebelum menulis CSS khusus.
- CSS custom hanya untuk kebutuhan yang tidak tercakup Bootstrap.
- Jangan menambahkan framework frontend terpisah.
- Bootstrap major/minor version: `TBD` dan harus dikunci sebelum implementasi.

## 4. Color tokens

Nilai berikut `PROPOSED`, bukan keputusan warna final. Validasi contrast ratio diperlukan.

### Light
| Token | Proposed | Usage |
|---|---|---|
| `--app-bg` | `#F8FAFC` | Latar halaman |
| `--surface` | `#FFFFFF` | Card/panel |
| `--surface-muted` | `#F1F5F9` | Area sekunder |
| `--text-primary` | `#0F172A` | Teks utama |
| `--text-secondary` | `#475569` | Teks pendukung |
| `--border` | `#E2E8F0` | Border/divider |
| `--primary` | `#2563EB` | Aksi utama |
| `--success` | `#15803D` | Keberhasilan |
| `--warning` | `#B45309` | Peringatan |
| `--danger` | `#B91C1C` | Kesalahan/destruktif |
| `--info` | `#0369A1` | Informasi |

### Dark
| Token | Proposed | Usage |
|---|---|---|
| `--app-bg` | `#0F172A` | Latar halaman |
| `--surface` | `#1E293B` | Card/panel |
| `--surface-muted` | `#334155` | Area sekunder |
| `--text-primary` | `#F8FAFC` | Teks utama |
| `--text-secondary` | `#CBD5E1` | Teks pendukung |
| `--border` | `#475569` | Border/divider |
| `--primary` | `#60A5FA` | Aksi utama |
| `--success` | `#4ADE80` | Keberhasilan |
| `--warning` | `#FBBF24` | Peringatan |
| `--danger` | `#F87171` | Kesalahan/destruktif |
| `--info` | `#7DD3FC` | Informasi |

### Semantic state mapping
| State | Label |
|---|---|
| Task priority | Rendah / Sedang / Tinggi |
| Task status | Belum Dikerjakan / Sedang Dikerjakan / Selesai |
| Study status | Direncanakan / Sedang Berlangsung / Selesai |
| Enrollment | Terdaftar / Ditolak / Dibatalkan / Selesai |
| Archive | Diarsipkan |
| Academic task | Terbuka / Ditutup |

Warna semantic hanya alat bantu; label harus selalu tersedia.

## 5. Typography

Proposed font stack: system UI sans-serif.

| Token | Size | Weight | Usage |
|---|---:|---|---|
| Display | 2rem | 700 | Heading utama terbatas |
| H1 | 1.75rem | 700 | Judul halaman |
| H2 | 1.375rem | 600 | Section |
| H3 | 1.125rem | 600 | Card heading |
| Body | 1rem | 400 | Konten |
| Body small | .875rem | 400 | Metadata |
| Label | .875rem | 600 | Form labels |
| Caption | .75rem | 400 | Keterangan sekunder |

Gunakan rem dan biarkan browser zoom bekerja. Jangan mengunci font scaling.

## 6. Spacing and layout

Base spacing unit: 4px (proposed).
- 4px: micro gap.
- 8px: compact gap.
- 12px: dense group.
- 16px: standard control/card spacing.
- 24px: section spacing.
- 32px: major section separation.
- 48px: large page separation.

Page layout:
- Desktop: sidebar + header + content.
- Mobile: offcanvas/compact navigation + content.
- Forms: single column on narrow viewport; optional two columns on wide viewport for independent fields.
- Tables: responsive wrapper or card presentation.
- Avoid fixed heights for content-heavy panels.

## 7. Shape, border, elevation

Proposed radii:
- Small: 4px.
- Standard: 8px.
- Large: 12px.
- Pill: 999px only for compact badges.

Prefer subtle borders and restrained shadows. Avoid excessive nested cards and heavy elevation.

## 8. Iconography

Choose one consistent icon set compatible with Blade/Bootstrap. Bootstrap Icons is a proposed option, not final. Icon-only actions require accessible names and tooltips where appropriate. Do not use icon color alone to communicate meaning.

## 9. Component states

Every interactive component must define:
- default;
- hover (pointer devices);
- focus-visible;
- active;
- disabled;
- loading where applicable;
- invalid where applicable.

Focus ring must remain visible in both themes.

## 10. Light/Dark theme

User decision: both Light and Dark themes.

Implementation recommendation:
- CSS custom properties for semantic tokens.
- Root `data-theme="light|dark"` attribute.
- Persist user preference server-side if a user-preference field is approved; otherwise use a clearly defined session/cookie approach.
- Theme switch must not modify application data or permissions.
- Theme preference persistence mechanism: `TBD`.
- Implementasi awal Phase 2 (`USER-DECIDED`, REC-10, 2026-11-18): preferensi disimpan pada cookie `theme` dan diterapkan melalui `data-theme` di elemen `<html>` oleh layout server-rendered. Pendekatan ini tidak menambah kolom database dan tidak mengubah izin; bila kelak disetujui penyimpanan per akun, cookie tetap menjadi fallback untuk pengunjung.

## 11. Accessibility

- Target WCAG 2.2 AA as a design goal (`PROPOSED`).
- Ensure text contrast.
- Do not convey status using color alone.
- Keyboard access for navigation, menus, forms, dialogs.
- Respect reduced-motion preferences.
- Use semantic HTML landmarks and headings.
- Tables need headers and captions where appropriate.

## 12. CSS organization proposal

```text
resources/
├── css/
│   ├── app.css
│   ├── tokens.css
│   ├── layout.css
│   └── components.css
└── js/
    └── app.js
```

Final asset pipeline depends on Laravel version and build tooling selected during implementation.

## 13. Open decisions

- Final palette and brand accent.
- Final font family.
- Bootstrap version (`USER-DECIDED`: 5.3).
- Icon library (`USER-DECIDED`: Bootstrap Icons).
- Theme preference persistence.
- Formal accessibility conformance target.

Status implementasi Phase 2 (2026-11-18): token warna pada §4 dan skala pada §5–§7 dipakai sebagai nilai `PROPOSED` di `resources/css/tokens.css`. Perubahan nilai tidak memerlukan perubahan komponen karena seluruh komponen memakai custom property.
