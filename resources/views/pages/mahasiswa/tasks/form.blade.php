@php($editing = isset($task))

<form method="POST"
      action="{{ $editing ? route('mahasiswa.tasks.personal.update', $task) : route('mahasiswa.tasks.personal.store') }}">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="judul_tugas" class="form-label">Judul Tugas</label>
        <input type="text"
               id="judul_tugas"
               name="judul_tugas"
               value="{{ old('judul_tugas', $task->judul_tugas ?? '') }}"
               class="form-control @error('judul_tugas') is-invalid @enderror"
               maxlength="150"
               required>
        @error('judul_tugas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea id="deskripsi"
                  name="deskripsi"
                  rows="3"
                  class="form-control @error('deskripsi') is-invalid @enderror"
                  maxlength="2000">{{ old('deskripsi', $task->deskripsi ?? '') }}</textarea>
        @error('deskripsi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="deadline" class="form-label">Deadline</label>
            <input type="datetime-local"
                   id="deadline"
                   name="deadline"
                   value="{{ old('deadline', isset($task) ? $task->deadline?->format('Y-m-d\TH:i') : '') }}"
                   class="form-control @error('deadline') is-invalid @enderror"
                   required>
            @error('deadline')
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
                    <option value="{{ $course->id_mata_kuliah }}" @selected(old('id_mata_kuliah', $task->id_mata_kuliah ?? '') == $course->id_mata_kuliah)>
                        {{ $course->kode_mata_kuliah }} — {{ $course->nama_mata_kuliah }}
                    </option>
                @endforeach
            </select>
            @error('id_mata_kuliah')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="id_prioritas" class="form-label">Prioritas</label>
            <select id="id_prioritas"
                    name="id_prioritas"
                    class="form-select @error('id_prioritas') is-invalid @enderror"
                    required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id_prioritas }}" @selected(old('id_prioritas', $task->id_prioritas ?? '') == $priority->id_prioritas)>
                        {{ $priority->nama_prioritas }}
                    </option>
                @endforeach
            </select>
            @error('id_prioritas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="id_status" class="form-label">Status</label>
            <select id="id_status"
                    name="id_status"
                    class="form-select @error('id_status') is-invalid @enderror"
                    required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id_status }}" @selected(old('id_status', $task->id_status ?? '') == $status->id_status)>
                        {{ $status->nama_status }}
                    </option>
                @endforeach
            </select>
            @error('id_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan Perubahan' : 'Simpan Tugas' }}</button>
        <a href="{{ $editing ? route('mahasiswa.tasks.personal.show', $task) : route('mahasiswa.tasks.index') }}"
           class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
