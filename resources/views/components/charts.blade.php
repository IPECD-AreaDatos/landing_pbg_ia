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
            
            // Evitar conflictos - crear variable local para este gráfico
            let mainEvolutionChart;

            const chartData = @json($chart_data);
            const evo = chartData.evolution ?? [];              // [{año, valor}]
            
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
            
            const labels = evo.map(i => String(i.año));
            const seriesRaw = evo.map(i => Number(i.valor));                // VALOR COMPLETO (pesos 2004)

            const fmtCompact = new Intl.NumberFormat('es-AR', { notation: 'compact', compactDisplay: 'short', maximumFractionDigits: 1 });
            const fmtFull = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });

            mainEvolutionChart = new Chart(evolutionEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'PBG (pesos constantes 2004)',
                        data: seriesRaw,
                        borderColor: '#76B82A',
                        backgroundColor: 'rgba(118, 184, 42, 0.1)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointBorderWidth: 3,
                        pointBorderColor: '#fff',
                        pointBackgroundColor: '#76B82A',
                        pointHoverBackgroundColor: '#76B82A',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3,
                        // Agregar sombra al área
                        segment: {
                            borderColor: ctx => {
                                const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 400);
                                gradient.addColorStop(0, '#76B82A');
                                gradient.addColorStop(1, 'rgba(118, 184, 42, 0.6)');
                                return gradient;
                            }
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#76B82A',
                            borderWidth: 2,
                            cornerRadius: 8,
                            displayColors: false,
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: (ctx) => {
                                    const value = fmtFull.format(ctx.parsed.y);
                                    const prevValue = ctx.dataIndex > 0 ? seriesRaw[ctx.dataIndex - 1] : null;
                                    let change = '';
                                    if (prevValue) {
                                        const changePercent = ((ctx.parsed.y - prevValue) / prevValue * 100);
                                        const changeIcon = changePercent >= 0 ? '↗' : '↘';
                                        const changeColor = changePercent >= 0 ? '🟢' : '🔴';
                                        change = `\n${changeColor} ${changeIcon} ${changePercent >= 0 ? '+' : ''}${changePercent.toFixed(1)}% vs año anterior`;
                                    }
                                    return `💰 $${value} (pesos 2004)${change}`;
                                },
                                title: (ctx) => `📅 Año ${ctx[0].label}`
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

            // Usar los datos de sectores desde chart_data
            const chartData = @json($chart_data);
            const raw = chartData.sectors || [];
            const data = raw.filter(it => (it.letra || '').trim().toUpperCase() !== 'PBG'); // excluye total
            
            // Calcular porcentajes y ordenar por valor descendente
            const dataWithPercentage = data.map(item => {
                const totalPbg = chartData.total_pbg || 1;
                const percentage = (Number(item.valor) / totalPbg) * 100;
                return {
                    ...item,
                    percentage: percentage,
                    formattedPercentage: percentage.toFixed(1)
                };
            });
            
            // Ordenar por porcentaje descendente y tomar top 10
            const top = dataWithPercentage
                .sort((a, b) => b.percentage - a.percentage)
                .slice(0, 10);

            // Crear gradiente para las barras
            const ctx = sectorEl.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, '#76B82A');
            gradient.addColorStop(1, 'rgba(118, 184, 42, 0.7)');
            
            new Chart(sectorEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: top.map(it => it.descripcion.length > 30 ? 
                        it.descripcion.substring(0, 27) + '...' : it.descripcion),
                    datasets: [{
                        label: 'Participación (%)',
                        data: top.map(it => parseFloat(it.formattedPercentage)),
                        backgroundColor: gradient,
                        borderColor: '#76B82A',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBackgroundColor: '#5a9625',
                        hoverBorderColor: '#4a7b1f',
                        hoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#76B82A',
                            borderWidth: 2,
                            cornerRadius: 8,
                            displayColors: false,
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                title: (ctx) => {
                                    const item = top[ctx[0].dataIndex];
                                    return `📊 ${item.descripcion}`;
                                },
                                label: (ctx) => {
                                    const item = top[ctx.dataIndex];
                                    const pct = Number(ctx.parsed.y).toFixed(1) + '%';
                                    const valor = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 }).format(item.valor);
                                    return [`📈 Participación: ${pct}`, `💰 Valor: $${valor} (miles, 2004)`];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { 
                                color: 'rgba(241, 245, 249, 0.8)',
                                drawBorder: false
                            },
                            border: { display: false },
                            ticks: {
                                color: '#64748b',
                                font: { size: 12 },
                                callback: (v) => v.toFixed(1) + '%',
                                padding: 10
                            }
                        },
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { 
                                color: '#64748b', 
                                maxRotation: 45, 
                                minRotation: 0,
                                font: { size: 11 },
                                padding: 10
                            }
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeInOutQuart'
                    },
                    // Hover effects
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    }
                }
            });
        })();
    </script>
@endpush