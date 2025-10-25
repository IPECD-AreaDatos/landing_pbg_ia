<section class="evolution-section">
    <div class="container">
        <div class="evolution-card">
            <h2 class="section-title">Evolución Sectorial Interactiva
                ({{ $statistics['min_year'] }}–{{ $statistics['max_year'] }})</h2>
            <p class="section-subtitle">Seleccioná un sector para ver su evolución temporal y variación anual detallada</p>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="sectorSelect" class="form-label fw-bold">
                        <i class="fas fa-industry me-2"></i>Seleccionar Sector
                    </label>
                    <select id="sectorSelect" class="form-select form-select-lg">
                        <option value="">Selecciona un sector para analizar...</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Elige entre 16 sectores productivos + PBG total
                    </small>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <h4>Evolución del valor (millones $ de 2004)</h4>
                    <div class="chart-wrapper" style="height: 260px;">
                        <canvas id="sectorEvolutionChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h4>Variación anual (%)</h4>
                    <div class="chart-wrapper" style="height: 260px;">
                        <canvas id="sectorVariationChart"></canvas>
                    </div>
                </div>
            </div>

            <p class="text-muted mt-3" style="font-size:.9rem">
                * Valores en millones de pesos a precios constantes de 2004. La variación anual es interanual (YoY).
            </p>
        </div>
    </div>
</section>
@push('scripts')
    <script>
        (() => {
            let sectorEvolutionChart, sectorVariationChart;

            const elEvolution = document.getElementById('sectorEvolutionChart');
            const elVariation = document.getElementById('sectorVariationChart');
            const selector = document.getElementById('sectorSelect');

            const fmtFull = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });
            const fmtCompact = new Intl.NumberFormat('es-AR', { notation: 'compact', compactDisplay: 'short', maximumFractionDigits: 1 });
            const fmtPct1 = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 });

            // Si el valor del año previo es menor a este umbral, anulamos el YoY por “base muy baja”
            const YOY_BASE_THRESHOLD = 100_000; // $100K de 2004 (ajustá si querés)

            const showLoader = (canvas, txt = 'Cargando…') => {
                const p = canvas.parentElement;
                p.style.position = 'relative';
                let l = p.querySelector('.chart-loader');
                if (!l) {
                    l = document.createElement('div');
                    l.className = 'chart-loader';
                    l.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.85);color:#64748b;font-size:14px;border-radius:8px;backdrop-filter:blur(2px);z-index:10';
                    p.appendChild(l);
                }
                l.innerHTML = `<div><i class="fas fa-spinner fa-spin me-2"></i>${txt}</div>`;
            };
            const hideLoader = (canvas) => {
                const l = canvas.parentElement.querySelector('.chart-loader');
                if (l) l.remove();
            };

            async function getSectors() {
                const r = await fetch('{{ $api_base_url }}/latest');
                const j = await r.json();
                if (!j?.data?.data) return [];
                
                // Convertir datos de latest a formato {code, description}
                // Solo incluir sectores principales (1 letra) + PBG para el gráfico de evolución
                const sectors = j.data.data
                    .filter(item => item.letra.length === 1 || item.letra === 'PBG')
                    .map(item => ({
                        code: item.letra,
                        description: `${item.letra} - ${item.descripcion}`,
                        isMainSector: true
                    }));
                
                return sectors;
            }
            async function getSectorData(code) {
                const r = await fetch(`{{ $api_base_url }}/sector/${encodeURIComponent(code)}`);
                if (!r.ok) return null;
                const j = await r.json();
                
                // Para gráfico de evolución, solo queremos datos del sector específico
                // No incluir subsectores (filtrar solo filas donde letra === code exactamente)
                const allData = j?.data ?? [];
                const sectorData = allData.filter(item => item.letra === code);
                
                console.log(`Filtrando datos para sector ${code}: ${allData.length} filas totales, ${sectorData.length} del sector principal`);
                
                return sectorData;
            }

            // Alias para mantener compatibilidad
            const getSectorSeries = getSectorData;

            function pick(o, ...keys) { for (const k of keys) { if (o?.[k] !== undefined && o?.[k] !== null) return o[k]; } return null; }

            function normalizeSeries(rows) {
                const ordered = [...rows].sort((a, b) => (+(pick(a, 'año', 'year') || 0)) - (+(pick(b, 'año', 'year') || 0)));

                const years = ordered.map(r => String(pick(r, 'año', 'year')));

                // Valor COMPLETO (sin dividir) para ver bien sectores chicos
                const values = ordered.map(r => Number(pick(r, 'valor', 'value') || 0));

                // YoY: usar columna si viene; si no, calcular. Si base < threshold, anular (null).
                const yoy = ordered.map((r, i) => {
                    const col = pick(r, 'variacion_interanual', 'yoy_variation');
                    if (col !== null && col !== undefined) return +Number(col).toFixed(2);
                    if (i === 0) return null;
                    const prev = Number(pick(ordered[i - 1], 'valor', 'value') || 0);
                    const cur = Number(pick(r, 'valor', 'value') || 0);
                    if (prev <= YOY_BASE_THRESHOLD) return null; // base demasiado baja = “s/d”
                    return +(((cur - prev) / prev) * 100).toFixed(2);
                });

                return { years, values, yoy };
            }

            function renderEvolution(labels, data) {
                sectorEvolutionChart?.destroy();
                sectorEvolutionChart = new Chart(elEvolution.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Valor (pesos constantes 2004)',
                            data,
                            borderColor: '#76B82A',
                            backgroundColor: 'rgba(118,184,42,.12)',
                            borderWidth: 3, fill: true, tension: .35, pointRadius: 2.5, pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (c) => ` $${fmtFull.format(c.parsed.y)} (2004)` } }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    color: '#64748b',
                                    callback: (v) => fmtCompact.format(v)
                                }
                            },
                            x: { grid: { color: '#f1f5f9' }, ticks: { color: '#64748b', maxRotation: 0 } }
                        },
                        interaction: { mode: 'index', intersect: false }
                    }
                });
            }

            function renderVariation(labels, yoy) {
                const colors = yoy.map(v => v === null ? '#9ca3af' : (v >= 0 ? '#10b981' : '#ef4444'));

                // Para que no “explote”, calculamos un rango razonable automático y lo acotamos
                const vals = yoy.filter(v => v !== null);
                const absMax = vals.length ? Math.max(...vals.map(v => Math.abs(v))) : 0;
                const hardCap = 250; // % máximo que vamos a mostrar (tooltip sigue mostrando el real si querés)
                const ymax = Math.min(Math.ceil((absMax + 10) / 50) * 50, hardCap);

                sectorVariationChart?.destroy();
                sectorVariationChart = new Chart(elVariation.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Variación YoY (%)',
                            data: yoy.map(v => v ?? 0),
                            backgroundColor: colors,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (c) => {
                                        const v = yoy[c.dataIndex];
                                        return v === null ? ' s/d' : ` ${v > 0 ? '+' : ''}${fmtPct1.format(v)}%`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                min: -ymax, max: ymax,
                                grid: { color: '#f1f5f9' },
                                ticks: { color: '#64748b', callback: (v) => fmtPct1.format(v) + '%' }
                            },
                            x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 0 } }
                        }
                    }
                });
            }

            async function populateSelectorIfNeeded() {
                // Verificar si el selector está vacío o solo tiene el placeholder
                if (selector.options.length > 1) {
                    console.log('✅ Selector ya popolado con', selector.options.length, 'opciones');
                    return;
                }
                
                try {
                    console.log('🔄 Cargando sectores desde API...');
                    const sectors = await getSectors(); // [{code, description}]
                    console.log('📊 Sectores obtenidos:', sectors);
                    
                    selector.innerHTML = '<option value="">Selecciona un sector para analizar...</option>';
                    
                    // Ordenar sectores: PBG primero, luego A-P
                    const sortedSectors = sectors.sort((a, b) => {
                        if (a.code === 'PBG') return -1;
                        if (b.code === 'PBG') return 1;
                        return a.code.localeCompare(b.code);
                    });
                    
                    console.log('🔄 Agregando', sortedSectors.length, 'sectores principales al selector');
                    
                    // Agregar solo sectores principales (sin agrupación)
                    sortedSectors.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.code; 
                        opt.textContent = s.description;
                        selector.appendChild(opt);
                    });
                    
                    // Elegimos por defecto PBG si existe, sino Comercio (G)
                    const prefer = ['PBG', 'G', 'A', 'K', 'D', 'L'];
                    const found = sortedSectors.find(s => prefer.includes(s.code));
                    if (found) {
                        console.log('🎯 Seleccionando sector por defecto:', found.code);
                        selector.value = found.code;
                    }
                    
                    console.log('✅ Selector poblado exitosamente');
                } catch (e) {
                    console.error('❌ Error loading sectors:', e);
                    selector.innerHTML = '<option value="">❌ Error cargando sectores - Verifica conexión API</option>';
                }
            }

            async function refresh() {
                try {
                    const code = selector.value;
                    console.log('🔄 Refresh solicitado para sector:', code);
                    
                    if (!code) {
                        console.log('⚠️ Sin sector seleccionado, limpiando gráficos');
                        renderEvolution([], []); 
                        renderVariation([], []);
                        return;
                    }
                    
                    showLoader(elEvolution, 'Cargando evolución...'); 
                    showLoader(elVariation, 'Cargando variaciones...');
                    
                    console.log('📡 Obteniendo datos para sector:', code);
                    const rows = await getSectorSeries(code);
                    console.log('📊 Datos recibidos:', rows ? rows.length : 0, 'filas');
                    
                    if (!rows || rows.length === 0) {
                        throw new Error('No se encontraron datos para este sector');
                    }
                    
                    const { years, values, yoy } = normalizeSeries(rows);
                    console.log('✨ Datos normalizados - años:', years.length, 'valores:', values.length);
                    
                    renderEvolution(years, values);
                    renderVariation(years, yoy);
                    console.log('✅ Gráficos renderizados exitosamente');
                } catch (e) {
                    console.error('❌ Error loading sector data:', e);
                    // Mostrar mensaje de error en lugar de gráficos vacíos
                    elEvolution.parentElement.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-exclamation-triangle me-2"></i>Error al cargar datos del sector</div>';
                    elVariation.parentElement.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-exclamation-triangle me-2"></i>Error al cargar datos del sector</div>';
                } finally {
                    hideLoader(elEvolution); 
                    hideLoader(elVariation);
                }
            }

            (async () => {
                await populateSelectorIfNeeded();
                selector.addEventListener('change', refresh);
                await refresh();
            })();
        })();
    </script>
@endpush