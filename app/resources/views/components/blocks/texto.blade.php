@props(['data'])
<div class="section">
    <div class="container content-block">
        <div class="body">{!! $data['content'] ?? '' !!}</div>
    </div>
</div>
