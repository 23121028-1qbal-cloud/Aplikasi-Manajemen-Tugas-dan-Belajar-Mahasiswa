@if (session('temporary_password'))
    @php($temporary = session('temporary_password'))
    <div class="alert alert-warning" role="alert">
        <p class="fw-semibold mb-1">Temporary password untuk <code>{{ $temporary['username'] }}</code></p>
        <p class="mb-1"><code class="fs-6">{{ $temporary['password'] }}</code></p>
        <p class="small mb-0">
            Tampilkan sekali kepada pengguna melalui kanal aman. Nilai ini tidak disimpan dalam log
            dan tidak dapat ditampilkan ulang.
        </p>
    </div>
@endif
