@php($year = now()->year)
<footer class="border-top px-3 px-lg-4 py-3 small text-body-secondary">
    {{ config('app.name') }} &middot; {{ $year }}
</footer>
