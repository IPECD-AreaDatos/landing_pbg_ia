<section class="sectors-section">
    <div class="container">
        <h2 class="section-title">Análisis Sectorial del PBG {{ $statistics['max_year'] }}</h2>
        <p class="section-subtitle">
            Contribución de cada sector económico al Producto Bruto Geográfico de Corrientes. 
            <br><small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Haz clic en cualquier sector principal para explorar sus subcategorías detalladas
            </small>
        </p>

        @php
            // Nueva paleta con los colores actualizados
            $palette = ['#76B82A', '#246638', '#F7A600', '#729BD1', '#596692', '#4D4D4D'];

            // Íconos según sector (ajustá a gusto)
            $icons = [
                'Agricultura' => 'seedling',
                'ganadería' => 'cow',        // si usás FontAwesome Pro podés cambiarlo
                'silvicultura' => 'tree',
                'Pesca' => 'fish',
                'minas' => 'mountain',
                'Industria' => 'industry',
                'Electricidad' => 'bolt',
                'Construcción' => 'building',
                'Comercio' => 'store',
                'Hotelería' => 'hotel',
                'Transporte' => 'truck',
                'Intermediación' => 'landmark',
                'Inmobiliarias' => 'building',
                'Administración' => 'landmark',
                'Enseñanza' => 'graduation-cap',
                'Servicios sociales' => 'hands-helping',
                'Servicios comunitarios' => 'users',
                'Servicio doméstico' => 'broom'
            ];

            // Función simple para elegir ícono por coincidencia
            function iconFor($name, $icons)
            {
                foreach ($icons as $needle => $icon) {
                    if (stripos($name, $needle) !== false)
                        return $icon;
                }
                return 'chart-pie'; // default
            }
        @endphp

        <div class="sector-grid">
            @foreach($pbgData->sortByDesc('value')->values() as $i => $sector)
                @php
                    $bg = $palette[$i % count($palette)];
                    $icon = iconFor($sector->sector, $icons);
                    $yoy = $sector->yoy; // puede ser null si no hay año previo
                    $hasSubsectors = strlen($sector->code) === 1; // Solo sectores A-P tienen subsectores
                @endphp

                <div class="sector-card {{ $hasSubsectors ? 'clickable' : '' }}" 
                     @if($hasSubsectors) 
                     data-sector-code="{{ $sector->code }}" 
                     onclick="toggleSubsectors(this)" 
                     @endif>
                    
                    <div class="sector-main">
                        <div class="sector-icon" style="background-color: {{ $bg }}">
                            <i class="fas fa-{{ $icon }}"></i>
                        </div>

                        <div class="sector-content">
                            <div class="sector-header">
                                <div class="sector-name">{{ $sector->sector }}</div>
                                @if($hasSubsectors)
                                    <div class="expand-indicator">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="sector-row">
                                <div class="sector-value">
                                    {{ number_format($sector->value, 1) }}%
                                    <small class="text-muted d-block">participación</small>
                                </div>

                                <div class="sector-amount">
                                    ${{ number_format($sector->value_absolute, 0, ',', '.') }}
                                    <small class="text-muted d-block">{{ $statistics['max_year'] }} · en miles de pesos precios 2004</small>
                                </div>
                            </div>

                            <div class="sector-growth-badge
                      {{ is_null($yoy) ? 'neutral' : ($yoy >= 0 ? 'up' : 'down') }}">
                                @if (is_null($yoy))
                                    s/d YoY
                                @else
                                    {{ $yoy >= 0 ? '+' : '' }}{{ number_format($yoy, 2) }}% YoY
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($hasSubsectors)
                        <div class="subsectors-container" style="display: none;">
                            <div class="subsectors-loader">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span>Cargando subcategorías...</span>
                                </div>
                            </div>
                            <div class="subsectors-header" style="display: none;">
                                <span class="subsectors-count"></span>
                                <span class="scroll-hint">Desliza para ver más ↓</span>
                            </div>
                            <div class="subsectors-grid"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    async function toggleSubsectors(cardElement) {
        const sectorCode = cardElement.dataset.sectorCode;
        const subsectorsContainer = cardElement.querySelector('.subsectors-container');
        const subsectorsGrid = cardElement.querySelector('.subsectors-grid');
        const loader = cardElement.querySelector('.subsectors-loader');
        
        // Si ya está expandido, colapsar
        if (cardElement.classList.contains('expanded')) {
            cardElement.classList.remove('expanded');
            subsectorsContainer.style.display = 'none';
            return;
        }
        
        // Expandir
        cardElement.classList.add('expanded');
        subsectorsContainer.style.display = 'block';
        
        // Si ya tenemos datos cargados, no volver a cargar
        if (subsectorsGrid.children.length > 0) {
            loader.style.display = 'none';
            return;
        }
        
        try {
            // Cargar datos de subcategorías desde la API
            const response = await fetch(`/api/pbg/sector/${sectorCode}`);
            const data = await response.json();
            
            if (data.data && data.data.subsectors) {
                // Obtener el año más reciente
                const latestYear = {{ $statistics['max_year'] }};
                
                // Renderizar subcategorías
                renderSubsectors(cardElement, data.data.subsectors, latestYear);
                loader.style.display = 'none';
            } else {
                throw new Error('No se encontraron subcategorías');
            }
        } catch (error) {
            console.error('Error loading subsectors:', error);
            subsectorsGrid.innerHTML = '<div class="text-center text-muted p-3">Error al cargar subcategorías</div>';
            loader.style.display = 'none';
        }
    }
    
    function renderSubsectors(cardElement, subsectors, latestYear) {
        const container = cardElement.querySelector('.subsectors-grid');
        const header = cardElement.querySelector('.subsectors-header');
        const countElement = cardElement.querySelector('.subsectors-count');
        const scrollHint = cardElement.querySelector('.scroll-hint');
        
        container.innerHTML = '';
        
        // Filtrar y ordenar subsectores por valor del año más reciente
        // Excluir el primer subsector que es el mismo que el sector principal
        const subsectorsWithLatestData = subsectors
            .map(subsector => {
                const latestData = subsector.data.find(d => d.year === latestYear);
                return latestData ? { ...subsector, latest: latestData } : null;
            })
            .filter(Boolean)
            .sort((a, b) => b.latest.value - a.latest.value)
            .slice(1); // Remover el primer elemento que es el sector principal
        
        // Mostrar header con contador
        if (subsectorsWithLatestData.length > 0) {
            header.style.display = 'flex';
            countElement.textContent = `${subsectorsWithLatestData.length} subcategorías`;
            
            // Mostrar hint de scroll solo si hay más de 5 items
            if (subsectorsWithLatestData.length > 5) {
                scrollHint.classList.remove('hidden');
            } else {
                scrollHint.classList.add('hidden');
            }
        }
        
        subsectorsWithLatestData.forEach(subsector => {
            const { latest } = subsector;
            const yoyClass = latest.yoy_variation === null ? 'neutral' : 
                           (latest.yoy_variation >= 0 ? 'up' : 'down');
            
            const yoyText = latest.yoy_variation === null ? 's/d' : 
                          `${latest.yoy_variation >= 0 ? '+' : ''}${Number(latest.yoy_variation).toFixed(1)}%`;
            
            const subsectorElement = document.createElement('div');
            subsectorElement.className = 'subsector-item';
            subsectorElement.innerHTML = `
                <div class="subsector-name">${subsector.description}</div>
                <div class="subsector-value">
                    $${Math.round(latest.value).toLocaleString('es-AR')}
                    <small class="text-muted d-block" style="font-size: 11px;">${latestYear} · en miles de pesos precios 2004</small>
                </div>
                <div class="subsector-growth ${yoyClass}">
                    ${yoyText} YoY
                </div>
            `;
            
            container.appendChild(subsectorElement);
        });
        
        // Agregar listener para ocultar hint cuando se hace scroll
        container.addEventListener('scroll', function() {
            if (this.scrollTop > 50) {
                scrollHint.classList.add('hidden');
            }
        });
        
        // Si no hay subcategorías, mostrar mensaje
        if (subsectorsWithLatestData.length === 0) {
            container.innerHTML = '<div class="text-center text-muted p-3">No se encontraron subcategorías para este año</div>';
            header.style.display = 'none';
        }
    }
</script>

<style>
    .sector-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .sector-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1200px) {
        .sector-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .sector-card {
        display: block;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 0 rgba(17, 24, 39, .04);
        transition: all 0.2s ease;
    }

    .sector-card.clickable {
        cursor: pointer;
    }

    .sector-card.clickable:hover {
        border-color: #76B82A;
        box-shadow: 0 4px 6px rgba(17, 24, 39, .1);
        transform: translateY(-1px);
    }

    .sector-card.expanded {
        border-color: #76B82A;
        box-shadow: 0 4px 6px rgba(17, 24, 39, .1);
    }

    .sector-main {
        display: grid;
        grid-template-columns: 60px 1fr;
        gap: 1rem;
        align-items: start;
    }

    .sector-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .sector-content {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .sector-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sector-name {
        font-weight: 700;
        color: #111827;
        flex: 1;
    }

    .expand-indicator {
        color: #6b7280;
        font-size: 14px;
        transition: transform 0.2s ease;
        margin-left: 8px;
    }

    .sector-card.expanded .expand-indicator {
        transform: rotate(180deg);
    }

    .sector-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
    }

    .sector-value {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.15rem;
    }

    .sector-amount {
        font-weight: 700;
        color: #246638;
        font-size: 1.05rem;
        text-align: right;
    }

    .sector-growth-badge {
        margin-top: .25rem;
        display: inline-block;
        padding: .25rem .5rem;
        border-radius: 999px;
        font-size: .85rem;
        font-weight: 600;
        width: max-content;
    }

    .sector-growth-badge.up {
        background: #ecfdf5;
        color: #047857;
    }

    .sector-growth-badge.down {
        background: #fef2f2;
        color: #b91c1c;
    }

    .sector-growth-badge.neutral {
        background: #f3f4f6;
        color: #4b5563;
    }

    .text-muted {
        color: #6b7280 !important;
    }

    /* Subsectors Styles */
    .subsectors-container {
        grid-column: 1 / -1;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .subsectors-loader {
        text-align: center;
        color: #6b7280;
        padding: 1rem;
        font-size: 14px;
    }

    .subsectors-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0 0.25rem;
    }

    .subsectors-count {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    .scroll-hint {
        font-size: 12px;
        color: #94a3b8;
        animation: fadeInOut 2s infinite;
    }

    @keyframes fadeInOut {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .scroll-hint.hidden {
        display: none;
    }

    .subsectors-grid {
        display: grid;
        gap: 0.75rem;
        margin-top: 0.5rem;
        max-height: 400px; /* Altura máxima para ~5 items */
        overflow-y: auto;
        padding-right: 4px; /* Espacio para scrollbar */
    }

    /* Scrollbar personalizado */
    .subsectors-grid::-webkit-scrollbar {
        width: 6px;
    }

    .subsectors-grid::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .subsectors-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .subsectors-grid::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .subsector-item {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: center;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 3px solid #76B82A;
    }

    .subsector-name {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .subsector-value {
        font-weight: 700;
        color: #0f172a;
        font-size: 14px;
        text-align: right;
    }

    .subsector-growth {
        padding: 0.125rem 0.375rem;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .subsector-growth.up {
        background: #ecfdf5;
        color: #047857;
    }

    .subsector-growth.down {
        background: #fef2f2;
        color: #b91c1c;
    }

    .subsector-growth.neutral {
        background: #f3f4f6;
        color: #4b5563;
    }
</style>