@props(['options', 'active' => 'todas', 'paramName' => 'categoria'])
{{-- $options: array asociativo [slug => label]. Navega recargando con query string (SSR, sin JS de filtro fantasma). --}}
<div class="filter-tabs" role="group" aria-label="Filtrar por categoría">
    <a href="{{ request()->fullUrlWithQuery([$paramName => null]) }}" aria-pressed="{{ $active === 'todas' ? 'true' : 'false' }}"
       style="text-decoration:none">Todas</a>
    @foreach($options as $slug => $label)
        <a href="{{ request()->fullUrlWithQuery([$paramName => $slug]) }}" aria-pressed="{{ $active === $slug ? 'true' : 'false' }}"
           style="text-decoration:none">{{ $label }}</a>
    @endforeach
</div>
