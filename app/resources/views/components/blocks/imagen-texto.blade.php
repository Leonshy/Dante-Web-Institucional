@props(['data'])
<div class="section">
    <div class="container" style="display:grid;gap:var(--spacing-8);grid-template-columns:1fr;align-items:center">
        <div class="hero-media" style="margin-bottom:0">
            @if(!empty($data['image']))
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($data['image']) }}" alt="" width="800" height="450" loading="lazy">
            @else
                <span class="seal-xl" aria-hidden="true"></span>
            @endif
        </div>
        <div>
            @if(!empty($data['title']))
                <h2>{{ $data['title'] }}</h2>
            @endif
            @if(!empty($data['text']))
                <div class="body">{!! $data['text'] !!}</div>
            @endif
        </div>
    </div>
</div>
