<section id="indicators" class="indicators-section">
    <style>
        .indicators-section { padding: 2rem 0; background: #f8fbfd; }
        .indicators-section .section-title { font-weight: 800; letter-spacing: .2px; color: #1f2937; text-align: center; margin-bottom: .25rem; }
        .indicators-section .section-subtitle { text-align: center; color: #6b7280; margin-bottom: 2.25rem; }

        .indicator-card {
            position: relative; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
            padding: 1.5rem 1.25rem; height: 100%; text-align: center;
            box-shadow: 0 1px 0 rgba(17,24,39,.04);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .indicator-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(17,24,39,.10); border-color: #dbeafe; }

        .indicator-icon { width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: .85rem; font-size: 18px; color: #fff; }
        .icon-primary { background: #76B82A; }
        .icon-success { background: #246638; }
        .icon-danger  { background: #ef4444; }
        .icon-info    { background: #729BD1; }
        .icon-warning { background: #F7A600; }

        .indicator-value { font-weight: 800; font-size: clamp(1.35rem, 1.15rem + 1vw, 1.75rem); line-height: 1.15; margin-bottom: .25rem; }
        .indicator-value.primary { color: #76B82A; }
        .indicator-value.success { color: #246638; }
        .indicator-value.danger  { color: #dc2626; }
        .indicator-value.info    { color: #729BD1; }
        .indicator-value.warning { color: #F7A600; }

        .indicator-label { color: #374151; font-weight: 600; font-size: .95rem; }
        .indicator-change { margin-top: .35rem; font-size: .85rem; font-weight: 500; }
        .change-success { color: #246638; } .change-danger { color: #dc2626; } .change-muted { color: #6b7280; }

        /* Igualar alturas aunque uses .col en vez de .col-md-4 */
        .indicators-section .row > .col { display: flex; }
        .indicators-section .row > .col > .indicator-card { width: 100%; }
    </style>

    <div class="container">
        <h2 class="section-title">Indicadores Clave</h2>
        <p class="section-subtitle">Principales métricas del desarrollo económico de Corrientes</p>

        <!-- Una sola fila, 4 columnas en pantallas grandes -->
        <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-4">

            {{-- 1) Valor PBG del último año --}}
            <div class="col">
                <div class="indicator-card">
                    <div class="indicator-icon icon-primary">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="indicator-value primary">
                        ${{ number_format(($chart_data['total_pbg'] ?? 0) / 1000000, 1) }}B
                    </div>
                    <div class="indicator-label">Valor PBG {{ $statistics['latest_pbg_year'] ?? 2024 }}</div>
                    <div class="indicator-change change-muted">Precios constantes 2004</div>
                </div>
            </div>

            {{-- 2) Variación interanual --}}
            <div class="col">
                <div class="indicator-card">
                    @if(isset($statistics['variation_yoy']) && $statistics['variation_yoy'] >= 0)
                        <div class="indicator-icon icon-success">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    @elseif(isset($statistics['variation_yoy']) && $statistics['variation_yoy'] < 0)
                        <div class="indicator-icon icon-danger">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    @else
                        <div class="indicator-icon icon-info">
                            <i class="fas fa-percentage"></i>
                        </div>
                    @endif
                    <div class="indicator-value {{ $statistics['variation_yoy'] >= 0 ? 'success' : 'danger' }}">
                        {{ $statistics['variation_yoy'] >= 0 ? '+' : '' }}{{ number_format($statistics['variation_yoy'], 1) }}%
                    </div>
                    <div class="indicator-label">
                        Variación interanual
                    </div>
                    <div class="indicator-change {{ $statistics['variation_yoy'] >= 0 ? 'change-success' : 'change-danger' }}">
                        {{ $statistics['latest_pbg_year'] - 1 }}→{{ $statistics['latest_pbg_year'] }}
                        @if($statistics['variation_yoy'] >= 0)
                            <i class="fas fa-arrow-up ms-1"></i>
                        @else
                            <i class="fas fa-arrow-down ms-1"></i>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3) Crecimiento promedio anual (CAGR) --}}
            <div class="col">
                <div class="indicator-card">
                    <div class="indicator-icon icon-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="indicator-value info">
                        +{{ number_format($statistics['cagr'], 1) }}%
                    </div>
                    <div class="indicator-label">Crecimiento promedio anual</div>
                    <div class="indicator-change change-muted">{{ $statistics['min_year'] }}–{{ $statistics['max_year'] }}</div>
                </div>
            </div>

            {{-- 4) Crecimiento acumulado (toda la serie) --}}
            <div class="col">
                <div class="indicator-card">
                    <div class="indicator-icon icon-warning">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <div class="indicator-value warning">
                        {{ number_format($statistics['growth_percentage'], 1) }}%
                    </div>
                    <div class="indicator-label">Crecimiento acumulado</div>
                    <div class="indicator-change change-muted">
                        {{ $statistics['min_year'] }}–{{ $statistics['max_year'] }}
                        @isset($statistics['abs_growth_billions'])
                            · <span class="change-success">+{{ number_format($statistics['abs_growth_billions'], 1) }}B</span>
                        @endisset
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
