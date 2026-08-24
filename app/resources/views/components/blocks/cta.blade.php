@props(['data'])
<div class="section">
    <div class="container">
        <div class="cta-block">
            <h2>{{ $data['title'] ?? '' }}</h2>
            @if(!empty($data['text']))
                <p>{{ $data['text'] }}</p>
            @endif
            @if(!empty($data['button_label']) && !empty($data['button_url']))
                <a class="btn btn-primary" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>
            @endif
        </div>
    </div>
</div>
