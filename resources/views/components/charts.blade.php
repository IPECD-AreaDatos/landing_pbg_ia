<section class="charts-section">
    <div class="container">
        <h2 class="section-title">Análisis General del PBG de Corrientes</h2>
        <p class="section-subtitle">
            Datos oficiales anuales del Producto Bruto Geográfico de Corrientes
            (2004–{{ $statistics['max_year'] }},
            precios constantes de 2004)
        </p>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="chart-container">
                    <h3 class="chart-title">Evolución del PBG
                        ({{ $statistics['min_year'] }}-{{ $statistics['max_year'] }})</h3>
                    <p class="chart-subtitle">Pesos a precios constantes de 2004</p>
                    <div class="chart-wrapper" style="height: 360px;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="chart-container">
                    <h3 class="chart-title">Distribución Sectorial {{ $statistics['max_year'] }}</h3>
                    <p class="chart-subtitle">Participación porcentual en el PBG total</p>
                    <div class="chart-wrapper" style="height: 360px;">
                        <canvas id="sectorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        /** ========= Evolución del PBG (serie PBG pura) ========= */
        (function () {
            const evolutionEl = document.getElementById('evolutionChart');
            if (!evolutionEl || !window.Chart) return;

            const chartData = @json($chartData);
            const evo = chartData.evolution_pbg_by_year ?? [];              // [{year, value}]
            
            // Validar datos antes de crear el gráfico
            if (!evo || evo.length === 0) {
                evolutionEl.style.display = 'none';
                evolutionEl.parentElement.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay datos disponibles</h5>
                        <p class="text-muted small">Selecciona un período con datos para visualizar el gráfico</p>
                    </div>
                `;
                return;
            }
            
            const labels = evo.map(i => String(i.year));
            const seriesRaw = evo.map(i => Number(i.value));                // VALOR COMPLETO (pesos 2004)

            const fmtCompact = new Intl.NumberFormat('es-AR', { notation: 'compact', compactDisplay: 'short', maximumFractionDigits: 1 });
            const fmtFull = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });

            new Chart(evolutionEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'PBG (pesos constantes 2004)',
                        data: seriesRaw,
                        borderColor: '#76B82A',
                        backgroundColor: 'rgba(118, 184, 42, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBorderWidth: 2,
                        pointBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#76B82A',
                            borderWidth: 1,
                            callbacks: {
                                // Tooltip con valor COMPLETO formateado en pesos
                                label: (ctx) => ` ${fmtFull.format(ctx.parsed.y)} $ (2004)`,
                                title: (ctx) => `Año ${ctx[0].label}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                color: '#64748b',
                                // Eje con notación compacta para legibilidad
                                callback: (value) => fmtCompact.format(value)
                            }
                        },
                        x: {
                            grid: { color: '#f1f5f9' },
                            ticks: { color: '#64748b' }
                        }
                    },
                    interaction: { mode: 'index', intersect: false },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        })();

        /** ========= Distribución sectorial (Top 10, excluye PBG) ========= */
        (function () {
            const sectorEl = document.getElementById('sectorChart');
            if (!sectorEl || !window.Chart) return;

            // Tus datos de sectores ya vienen como porcentajes en $pbgData (según tu DashboardController),
            // pero nos aseguramos de excluir "PBG" por si estuviera.
            const raw = @json($pbgData->sortByDesc('value')->values());
            const data = raw.filter(it => (it.sector || '').trim().toUpperCase() !== 'PBG'); // excluye total
            const top = data.slice(0, 10);

            new Chart(sectorEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: top.map(it => it.sector),
                    datasets: [{
                        label: 'Participación (%)',
                        data: top.map(it => Number(it.value)), // ya viene en %
                        backgroundColor: '#76B82A',
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                // Si además querés mostrar el valor absoluto en millones (lo tenés en value_absolute)
                                label: (ctx) => {
                                    const item = top[ctx.dataIndex];
                                    const pct = Number(ctx.parsed.y).toFixed(2) + '%';
                                    const abs = (item.value_absolute !== undefined)
                                        ? `  •  $${new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 }).format(item.value_absolute)}M`
                                        : '';
                                    return ` ${pct}${abs}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                color: '#64748b',
                                callback: (v) => v + '%'
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', maxRotation: 45, autoSkip: false }
                        }
                    }
                }
            });
        })();
    </script>
@endpush