@if (isset($errors) && $errors->any())
    <div id="validation-errors" class="alert alert-danger" role="alert" tabindex="-1">
        <p class="fw-semibold mb-1">Periksa kembali isian berikut:</p>
        <ul class="mb-0">
            @foreach ($errors->messages() as $field => $messages)
                @foreach ($messages as $message)
                    <li>
                        <a href="#{{ $field }}" class="alert-link">{{ $message }}</a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
