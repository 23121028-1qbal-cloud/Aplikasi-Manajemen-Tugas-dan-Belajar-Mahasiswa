@props([
    'id',
    'title',
    'message',
    'action',
    'method' => 'POST',
    'confirm' => 'Ya, lanjutkan',
    'variant' => 'danger',
])

<div class="modal fade"
     id="{{ $id }}"
     tabindex="-1"
     aria-labelledby="{{ $id }}-label"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h6" id="{{ $id }}-label">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup dialog"></button>
            </div>
            <div class="modal-body">
                {{ $message }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @if ($method !== 'POST')
                        @method($method)
                    @endif
                    {{ $slot ?? '' }}
                    <button type="submit" class="btn btn-{{ $variant }}">{{ $confirm }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
