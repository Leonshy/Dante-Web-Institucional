<section class="section reveal" style="background:var(--color-neutral-100)">
    <div class="container">
        <div class="section-head"><h2>Más de un siglo de historia</h2></div>
        <div class="stats-grid">
            @foreach($stats as $stat)
                <div>
                    <span class="stat-number">{{ $stat['value'] ?? '' }}{{ $stat['symbol'] ?? '' }}</span>
                    @if(!empty($stat['title']))
                        <p>{{ $stat['title'] }}</p>
                    @endif
                    @if(!empty($stat['description']))
                        <p class="caption">{{ $stat['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
