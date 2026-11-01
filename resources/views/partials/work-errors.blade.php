@if ($errors->any())
    <div class="work-alert work-alert-error" role="alert" id="formErrors">
        <ul class="work-alert-list">
            @foreach ($errors->messages() as $field => $messages)
                @foreach ($messages as $message)
                    <li><button type="button" class="work-alert-link" data-error-target="{{ $field }}">{{ $message }}</button></li>
                @endforeach
            @endforeach
        </ul>
        <p class="work-alert-tip">Select a message to jump to the field that needs fixing. Everything you entered has been kept.</p>
    </div>
@endif
