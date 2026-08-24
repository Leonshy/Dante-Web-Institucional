@props(['name', 'role' => null, 'photo' => null])
<div class="card" {{ $attributes }}>
    <div class="card-body" style="display:flex;gap:var(--spacing-4);align-items:center">
        <span class="avatar">
            @if($photo)
                <img src="{{ $photo }}" alt="" width="64" height="64" loading="lazy">
            @else
                {{ mb_substr($name, 0, 1) }}
            @endif
        </span>
        <div>
            <h3 style="font-size:16px">{{ $name }}</h3>
            @if($role)
                <p class="caption" style="margin:2px 0 0">{{ $role }}</p>
            @endif
        </div>
    </div>
</div>
