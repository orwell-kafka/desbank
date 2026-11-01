@if ($errors->any())
    <div class="work-alert work-alert-error" role="alert">
        @if ($errors->count() > 1)
            <ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        @else
            {{ $errors->first() }}
        @endif
    </div>
@endif
