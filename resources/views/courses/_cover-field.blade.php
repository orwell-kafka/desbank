@php $currentCover = isset($course) ? $course->coverUrl() : null; @endphp
<div class="work-field work-cover-field">
    <label class="work-label" for="coverImage">Cover picture <span class="work-optional">(optional)</span></label>
    <div class="work-cover-preview {{ $currentCover ? 'has-image' : '' }}" id="coverPreview" @if ($currentCover) style="--cover: url('{{ $currentCover }}');" @endif aria-hidden="true">
        <span id="coverPreviewLabel">{{ $currentCover ? '' : 'No picture chosen' }}</span>
    </div>
    <input type="file" id="coverImage" name="coverImage" accept="image/jpeg,image/png,image/webp" class="work-input {{ $errors->has('coverImage') ? 'is-invalid' : '' }}" aria-describedby="coverHint" @if ($errors->has('coverImage')) aria-invalid="true" @endif>
    <p class="work-hint" id="coverHint">JPG, PNG or WebP, up to 2 MB. It appears as a faint design behind this course on the course list and on its own page.</p>
    @if ($currentCover)
        <label class="work-cover-remove"><input type="checkbox" name="removeCover" value="1" class="work-check"> Remove the current picture</label>
    @endif
</div>
<script>
    (function () {
        var input = document.getElementById('coverImage');
        var box = document.getElementById('coverPreview');
        var label = document.getElementById('coverPreviewLabel');
        if (!input || !box) return;
        var original = box.style.getPropertyValue('--cover');
        var originalText = label.textContent;
        var url = null;
        input.addEventListener('change', function () {
            if (url) { URL.revokeObjectURL(url); url = null; }
            var file = input.files && input.files[0];
            if (file) {
                url = URL.createObjectURL(file);
                box.style.setProperty('--cover', "url('" + url + "')");
                box.classList.add('has-image');
                label.textContent = '';
            } else if (original) {
                box.style.setProperty('--cover', original);
                label.textContent = originalText;
            } else {
                box.style.removeProperty('--cover');
                box.classList.remove('has-image');
                label.textContent = originalText;
            }
        });
    })();
</script>
