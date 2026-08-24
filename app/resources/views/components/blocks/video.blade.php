@props(['data'])
<div class="section">
    <div class="container">
        @if(!empty($data['url']))
            <div class="hero-media" style="margin-bottom:0">
                <iframe src="{{ $data['url'] }}" title="Video institucional" width="100%" height="450" loading="lazy"
                        style="border:0;aspect-ratio:16/9" allowfullscreen></iframe>
            </div>
        @elseif(!empty($data['file']))
            <video controls width="100%" style="border-radius:var(--radius-lg)"
                   @if(!empty($data['cover'])) poster="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($data['cover']) }}" @endif>
                <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($data['file']) }}" type="video/mp4">
            </video>
        @endif
    </div>
</div>
