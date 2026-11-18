@php($editing = isset($activity))

<form method="POST"
      action="{{ $editing ? route('mahasiswa.study-activities.update', $activity) : route('mahasiswa.study-activities.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
        <input type="hidden" name="row_version" value="{{ old('row_version', $activity->row_version) }}">
    @endif

    @if ($errors->has('row_version'))
        <div class="alert alert-warning" role="alert">
            {{ $errors->first('row_version') }}
            <a href="{{ route('mahasiswa.study-activities.edit', $activity) }}" class="alert-link">Muat versi terbaru</a>.
        </div>
    @endif

    <div class="mb-3">
        <label for="judul_aktivitas" class="form-label">Judul Aktivitas</label>
        <input type="text"
               id="judul_aktivitas"
               name="judul_aktivitas"
               value="{{ old('judul_aktivitas', $activity->judul_aktivitas ?? '') }}"
               class="form-control @error('judul_aktivitas') is-invalid @enderror"
               maxlength="150"
               required>
        @error('judul_aktivitas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="materi_belajar" class="form-label">Materi Belajar</label>
        <textarea id="materi_belajar"
                  name="materi_belajar"
                  rows="3"
                  class="form-control @error('materi_belajar') is-invalid @enderror"
                  maxlength="2000">{{ old('materi_belajar', $activity->materi_belajar ?? '') }}</textarea>
        @error('materi_belajar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
            <input type="datetime-local"
                   id="waktu_mulai"
                   name="waktu_mulai"
                   value="{{ old('waktu_mulai', isset($activity) ? $activity->waktu_mulai?->format('Y-m-d\TH:i') : '') }}"
                   class="form-control @error('waktu_mulai') is-invalid @enderror"
                   required>
            @error('waktu_mulai')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="durasi_menit" class="form-label">Durasi (menit)</label>
            <input type="number"
                   id="durasi_menit"
                   name="durasi_menit"
                   value="{{ old('durasi_menit', $activity->durasi_menit ?? '') }}"
                   class="form-control @error('durasi_menit') is-invalid @enderror"
                   min="1"
                   max="65535"
                   required>
            @error('durasi_menit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="id_status_belajar" class="form-label">Status</label>
            <select id="id_status_belajar"
                    name="id_status_belajar"
                    class="form-select @error('id_status_belajar') is-invalid @enderror"
                    required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id_status_belajar }}" @selected(old('id_status_belajar', $activity->id_status_belajar ?? '') == $status->id_status_belajar)>
                        {{ $status->nama_status }}
                    </option>
                @endforeach
            </select>
            @error('id_status_belajar')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="id_mata_kuliah" class="form-label">Mata Kuliah (opsional)</label>
            <select id="id_mata_kuliah"
                    name="id_mata_kuliah"
                    class="form-select @error('id_mata_kuliah') is-invalid @enderror">
                <option value="">Tanpa mata kuliah</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id_mata_kuliah }}" @selected(old('id_mata_kuliah', $activity->id_mata_kuliah ?? '') == $course->id_mata_kuliah)>
                        {{ $course->kode_mata_kuliah }} — {{ $course->nama_mata_kuliah }}
                    </option>
                @endforeach
            </select>
            @error('id_mata_kuliah')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan Perubahan' : 'Simpan Aktivitas' }}</button>
        <a href="{{ $editing ? route('mahasiswa.study-activities.show', $activity) : route('mahasiswa.study-activities.index') }}"
           class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
