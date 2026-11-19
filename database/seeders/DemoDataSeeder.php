<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Enums\Semester;
use App\Enums\TaskType;
use App\Models\AktivitasBelajar;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $userDosen = User::firstOrCreate(
            ['username' => 'dosen1'],
            ['password' => Hash::make('password'), 'role' => Role::GuruDosen, 'is_active' => true]
        );
        $dosen = GuruDosen::firstOrCreate(
            ['id_user' => $userDosen->id_user],
            ['nip_guru' => '198001012005011001', 'nama_lengkap' => 'Dr. Budi Santoso', 'is_archived' => false]
        );

        $userMhs = User::firstOrCreate(
            ['username' => 'mhs1'],
            ['password' => Hash::make('password'), 'role' => Role::Mahasiswa, 'is_active' => true]
        );
        $mhs = Mahasiswa::firstOrCreate(
            ['id_user' => $userMhs->id_user],
            ['npm' => '2024000001', 'nama_lengkap' => 'Andi Pratama', 'is_archived' => false]
        );

        $mk = MataKuliah::firstOrCreate(
            ['kode_mata_kuliah' => 'IF301'],
            ['nama_mata_kuliah' => 'Pemrograman Web', 'guru_dosen_id' => $dosen->id_guru_dosen, 'is_archived' => false, 'row_version' => 1]
        );

        \App\Models\Enrollment::firstOrCreate(
            ['id_mahasiswa' => $mhs->id_mahasiswa, 'id_mata_kuliah' => $mk->id_mata_kuliah],
            ['tahun_akademik' => '2026/2027', 'semester' => Semester::Ganjil, 'tanggal_daftar' => now()->toDateString(), 'status' => EnrollmentStatus::Terdaftar]
        );

        $idPrioritasSedang = \App\Models\Prioritas::query()->where('nama_prioritas', 'Sedang')->value('id_prioritas');
        $idStatusBelum = StatusTugas::query()->where('nama_status', 'Belum Dikerjakan')->value('id_status');
        $idStatusSedang = StatusTugas::query()->where('nama_status', 'Sedang Dikerjakan')->value('id_status');
        $idStatusSelesai = StatusTugas::query()->where('nama_status', 'Selesai')->value('id_status');

        $tugasAkademik = Tugas::firstOrCreate(
            ['judul_tugas' => 'Laporan Praktikum Pemrograman Web', 'id_mahasiswa_pemilik' => $mhs->id_mahasiswa],
            [
                'id_mata_kuliah' => $mk->id_mata_kuliah,
                'id_prioritas' => $idPrioritasSedang,
                'id_status' => $idStatusSedang,
                'id_user' => $userMhs->id_user,
                'id_pembuat_guru_dosen' => $dosen->id_guru_dosen,
                'task_type' => TaskType::Academic,
                'deskripsi' => 'Buat laporan praktikum modul 1-3.',
                'deadline' => now()->addDays(10),
                'is_closed' => false,
                'is_archived' => false,
                'row_version' => 1,
            ]
        );

        ProgresTugasAkademik::firstOrCreate(
            ['id_tugas' => $tugasAkademik->id_tugas, 'id_mahasiswa' => $mhs->id_mahasiswa],
            ['id_status' => $idStatusSedang, 'id_user_diubah_oleh' => $userMhs->id_user, 'tanggal_ubah' => now()]
        );

        $tugasTutup = Tugas::firstOrCreate(
            ['judul_tugas' => 'Tugas Mingguan 1', 'id_mahasiswa_pemilik' => $mhs->id_mahasiswa],
            [
                'id_mata_kuliah' => $mk->id_mata_kuliah,
                'id_prioritas' => $idPrioritasSedang,
                'id_status' => $idStatusSelesai,
                'id_user' => $userMhs->id_user,
                'id_pembuat_guru_dosen' => $dosen->id_guru_dosen,
                'task_type' => TaskType::Academic,
                'deskripsi' => 'Sudah ditutup.',
                'deadline' => now()->subDays(3),
                'is_closed' => true,
                'is_archived' => false,
                'row_version' => 1,
            ]
        );

        ProgresTugasAkademik::firstOrCreate(
            ['id_tugas' => $tugasTutup->id_tugas, 'id_mahasiswa' => $mhs->id_mahasiswa],
            ['id_status' => $idStatusSelesai, 'id_user_diubah_oleh' => $userMhs->id_user, 'tanggal_ubah' => now()]
        );

        Tugas::firstOrCreate(
            ['judul_tugas' => 'Belajar Laravel Blade', 'id_mahasiswa_pemilik' => $mhs->id_mahasiswa, 'task_type' => TaskType::Personal],
            [
                'id_mata_kuliah' => null,
                'id_prioritas' => $idPrioritasSedang,
                'id_status' => $idStatusBelum,
                'id_user' => $userMhs->id_user,
                'task_type' => TaskType::Personal,
                'deskripsi' => 'Review materi blade templating.',
                'deadline' => now()->addDays(5),
                'is_closed' => false,
                'is_archived' => false,
                'row_version' => 1,
            ]
        );

        Tugas::firstOrCreate(
            ['judul_tugas' => 'Baca buku Clean Code', 'id_mahasiswa_pemilik' => $mhs->id_mahasiswa, 'is_archived' => true],
            [
                'id_mata_kuliah' => null,
                'id_prioritas' => $idPrioritasSedang,
                'id_status' => $idStatusSelesai,
                'id_user' => $userMhs->id_user,
                'task_type' => TaskType::Personal,
                'deskripsi' => 'Selesai dibaca.',
                'deadline' => now()->subDays(10),
                'is_closed' => false,
                'is_archived' => true,
                'tanggal_arsip' => now(),
                'row_version' => 1,
            ]
        );

        if (! Notifikasi::where('dedup_key', 'tugas_baru:'.$tugasAkademik->id_tugas)->exists()) {
            Notifikasi::create([
                'id_user' => $userMhs->id_user,
                'tipe' => NotificationType::AcademicTaskAssigned,
                'judul' => 'Tugas baru: Laporan Praktikum Pemrograman Web',
                'pesan' => 'Dosen menambahkan tugas baru.',
                'dedup_key' => 'tugas_baru:'.$tugasAkademik->id_tugas,
                'resource_type' => 'tugas',
                'resource_id' => $tugasAkademik->id_tugas,
                'tanggal_dibuat' => now(),
                'is_read' => false,
            ]);
        }
        if (! Notifikasi::where('dedup_key', 'deadline:'.$tugasAkademik->id_tugas)->exists()) {
            Notifikasi::create([
                'id_user' => $userMhs->id_user,
                'tipe' => NotificationType::AcademicTaskDeadline,
                'judul' => 'Deadline dekat: Laporan Praktikum Pemrograman Web',
                'pesan' => 'Tugas ini akan jatuh tempo dalam 2 hari.',
                'dedup_key' => 'deadline:'.$tugasAkademik->id_tugas,
                'resource_type' => 'tugas',
                'resource_id' => $tugasAkademik->id_tugas,
                'tanggal_dibuat' => now(),
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        AktivitasBelajar::firstOrCreate(
            [
                'id_mahasiswa' => $mhs->id_mahasiswa,
                'judul_aktivitas' => 'Review materi Blade templating',
                'tanggal_dibuat' => now()->toDateString(),
            ],
            [
                'id_mata_kuliah' => $mk->id_mata_kuliah,
                'id_status_belajar' => 3,
                'materi_belajar' => 'Layout, komponen, dan partial Blade.',
                'waktu_mulai' => now()->subHours(3),
                'durasi_menit' => 90,
            ]
        );
    }
}
