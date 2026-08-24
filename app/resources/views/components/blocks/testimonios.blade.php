@props(['data'])
<div class="section">
    <div class="container">
        <div class="cards-grid-2">
            @foreach($data['items'] ?? [] as $item)
                <div class="testimonial-card">
                    <div class="body">{!! $item['text'] ?? '' !!}</div>
                    <div class="who">
                        <span class="avatar">
                            @if(!empty($item['photo']))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['photo']) }}" alt="" width="48" height="48">
                            @else
                                {{ mb_substr($item['name'] ?? '?', 0, 1) }}
                            @endif
                        </span>
                        <div>
                            <strong>{{ $item['name'] ?? '' }}</strong>
                            @if(!empty($item['role']))
                                <p class="caption" style="margin:0">{{ $item['role'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
