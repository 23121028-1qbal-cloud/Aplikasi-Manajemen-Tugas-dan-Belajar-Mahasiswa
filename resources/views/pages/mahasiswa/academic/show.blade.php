@extends('layouts.app')

@section('title', $task->judul_tugas)

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">
                    Tugas Akademik
                    @if ($task->is_closed)
                        <span class="badge text-bg-secondary ms-1">Ditutup</span>
                    @else
                        <span class="badge text-bg-success ms-1">Terbuka</span>
                    @endif
                </p>
                <h2 class="h5 mb-0">{{ $task->judul_tugas }}</h2>
            </div>
            <a href="{{ route('mahasiswa.tasks.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Mata Kuliah</dt>
            <dd class="col-sm-9">
                {{ $task->mataKuliah?->kode_mata_kuliah }} — {{ $task->mataKuliah?->nama_mata_kuliah }}
            </dd>

            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">{{ $task->deskripsi ?: '—' }}</dd>

            <dt class="col-sm-3">Deadline</dt>
            <dd class="col-sm-9">{{ $task->deadline?->format('d M Y H:i') }}</dd>

            <dt class="col-sm-3">Prioritas</dt>
            <dd class="col-sm-9">{{ $task->prioritas?->nama_prioritas }}</dd>

            <dt class="col-sm-3">Progres Saya</dt>
            <dd class="col-sm-9">
                <span class="badge text-bg-light border">{{ $progress->statusTugas?->nama_status }}</span>
                <span class="small text-body-secondary ms-1">
                    diubah {{ $progress->tanggal_ubah?->format('d M Y H:i') }} oleh {{ $progress->diubahOleh?->username ?? '—' }}
                </span>
            </dd>
        </dl>
    </div>

    <div class="app-card p-4 mt-3">
        <h3 class="h6">Perbarui Progres</h3>
        <p class="small text-body-secondary">
            Anda hanya dapat mengubah progres milik sendiri. Definisi tugas tidak dapat diubah dari halaman ini.
        </p>

        @if ($errors->has('row_version') || $errors->has('id_status'))
            <div class="alert alert-warning" role="alert">
                {{ $errors->first('row_version') ?: $errors->first('id_status') }}
                <a href="{{ route('mahasiswa.academic-tasks.show', $task) }}" class="alert-link">Muat versi terbaru</a>.
            </div>
        @endif

        @if ($task->is_archived)
            <div class="alert alert-secondary mb-0" role="status">
                Tugas ini telah diarsipkan. Progres terkunci untuk semua pengguna.
            </div>
        @elseif ($task->is_closed)
            <div class="alert alert-warning mb-0" role="status">
                Tugas ini telah ditutup. Progres mahasiswa tidak dapat diubah; hubungi dosen jika perlu koreksi.
            </div>
        @else
            <form method="POST" action="{{ route('mahasiswa.academic-tasks.progress.update', $task) }}" style="max-width: 420px;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="row_version" value="{{ old('row_version', $progress->row_version) }}">

                <div class="mb-3">
                    <label for="id_status" class="form-label">Status Progres</label>
                    <select id="id_status" name="id_status" class="form-select @error('id_status') is-invalid @enderror" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id_status }}" @selected(old('id_status', $progress->id_status) == $status->id_status)>
                                {{ $status->nama_status }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan Progres</button>
            </form>
        @endif
    </div>
@endsection
