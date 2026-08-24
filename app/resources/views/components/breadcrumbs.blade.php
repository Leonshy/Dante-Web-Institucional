@props(['items' => []])
{{-- $items: array de ['label' => string, 'url' => string|null] — el último sin url es la página actual --}}
@if(count($items) > 0)
<nav class="breadcrumbs container" aria-label="Ruta de navegación">
    <ol>
        <li><a href="{{ url('/') }}">Inicio</a></li>
        @foreach($items as $item)
            @if(!empty($item['url']) && !$loop->last)
                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @else
                <li aria-current="page">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
