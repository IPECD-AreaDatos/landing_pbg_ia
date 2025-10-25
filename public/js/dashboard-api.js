// Dashboard API Manager - Consume la API de Vercel
class DashboardAPI {
    constructor(apiBaseUrl) {
        this.apiBaseUrl = apiBaseUrl;
        this.cache = new Map();
        this.init();
    }

    async init() {
        try {
            await this.loadDashboardData();
        } catch (error) {
            console.error('Error initializing dashboard:', error);
            this.showError('Error cargando el dashboard');
        }
    }

    async fetchAPI(endpoint) {
        const url = `${this.apiBaseUrl}${endpoint}`;
        
        // Cache simple para evitar requests repetidos
        if (this.cache.has(url)) {
            return this.cache.get(url);
        }

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.cache.set(url, data);
            return data;
        } catch (error) {
            console.error(`Error fetching ${url}:`, error);
            throw error;
        }
    }

    async loadDashboardData() {
        try {
            // Mostrar loader
            this.showLoader();

            // Cargar datos principales de manera más robusta
            const chartData = await this.fetchAPI('/charts').catch(() => ({ data: null }));
            const latestData = await this.fetchAPI('/latest').catch(() => ({ data: null }));

            // Solo procesar datos si existen
            if (chartData && chartData.data) {
                this.updateIndicators(chartData.data, latestData);
                this.updateCharts(chartData.data);
                // Comentado: gráfico de evolución se maneja desde charts.blade.php
                // if (chartData.data.evolution) {
                //     this.updateEvolution(chartData.data.evolution);
                // }
            }

            if (latestData && latestData.data) {
                this.updateSectors(latestData);
            }

            // Ocultar loader
            this.hideLoader();

            console.log('Dashboard cargado exitosamente');

        } catch (error) {
            console.error('Error cargando dashboard:', error);
            this.hideLoader();
            this.showError('Error cargando los datos del dashboard');
        }
    }

    updateIndicators(chartData, latestData) {
        try {
            if (!chartData) return;

            // Calcular valores para los indicadores
            if (chartData.total_pbg) {
                const totalBillions = (chartData.total_pbg / 1_000_000_000).toFixed(2);
                this.updateElement('#total-value', `$${totalBillions}B`);
            }
            
            // Calcular crecimiento y CAGR de la evolución
            const evolution = chartData.evolution;
            if (evolution && evolution.length > 1) {
                try {
                    const firstYear = evolution[0];
                    const lastYear = evolution[evolution.length - 1];
                    const yearsSpan = lastYear.year - firstYear.year;
                    
                    if (firstYear.valor && lastYear.valor && yearsSpan > 0) {
                        const growthPercentage = ((lastYear.valor - firstYear.valor) / firstYear.valor * 100).toFixed(2);
                        const cagr = (Math.pow(lastYear.valor / firstYear.valor, 1 / yearsSpan) - 1) * 100;

                        this.updateElement('#growth-percentage', `${growthPercentage}%`);
                        this.updateElement('#cagr-value', `${cagr.toFixed(2)}%`);
                        this.updateElement('#years-span', `${evolution.length} años`);
                        this.updateElement('#latest-year', lastYear.year || lastYear.año);
                    }
                } catch (error) {
                    console.warn('Error calculando indicadores de evolución:', error);
                }
            }

            // Actualizar sector principal
            if (chartData.top_sectors && chartData.top_sectors.length > 0) {
                const topSector = chartData.top_sectors[0];
                this.updateElement('#max-sector', topSector.descripcion || 'N/A');
            }
        } catch (error) {
            console.warn('Error actualizando indicadores:', error);
        }
    }

    updateCharts(chartData) {
        // Gráfico de evolución deshabilitado - se maneja desde charts.blade.php
        // if (chartData.evolution && window.updateEvolutionChart) {
        //     window.updateEvolutionChart(chartData.evolution);
        // }

        // Actualizar gráfico de sectores
        if (chartData.sectors && window.updateSectorsChart) {
            window.updateSectorsChart(chartData.sectors);
        }

        // Actualizar gráfico de top sectores
        if (chartData.top_sectors && window.updateTopSectorsChart) {
            window.updateTopSectorsChart(chartData.top_sectors);
        }
    }

    updateSectors(latestData) {
        const sectorsContainer = document.getElementById('sectors-container');
        if (!sectorsContainer || !latestData.data) return;

        // Filtrar solo sectores principales (letra de 1 carácter, excluir PBG)
        const mainSectors = latestData.data.filter(item => 
            item.letra.length === 1 && item.letra !== 'PBG'
        );

        // Calcular total para porcentajes
        const total = mainSectors.reduce((sum, sector) => sum + parseFloat(sector.valor), 0);

        // Generar HTML para sectores
        const sectorsHTML = mainSectors.map(sector => {
            const percentage = ((sector.valor / total) * 100).toFixed(1);
            const variationClass = sector.variacion_interanual >= 0 ? 'text-success' : 'text-danger';
            const variationIcon = sector.variacion_interanual >= 0 ? '▲' : '▼';
            
            return `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card sector-card h-100">
                        <div class="card-body">
                            <h6 class="card-title">${sector.letra} - ${sector.descripcion}</h6>
                            <div class="sector-value">${percentage}%</div>
                            <div class="sector-variation ${variationClass}">
                                ${variationIcon} ${sector.variacion_interanual}%
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        sectorsContainer.innerHTML = sectorsHTML;
    }

    updateEvolution(evolutionData) {
        // Deshabilitado: este gráfico se maneja desde charts.blade.php
        // Evita conflictos con el gráfico estático principal
        return;
        
        /*
        if (!evolutionData || !window.Chart) return;

        const ctx = document.getElementById('evolutionChart');
        if (!ctx) return;

        // Preparar datos para Chart.js
        const labels = evolutionData.map(item => item.año || item.year);
        const data = evolutionData.map(item => item.valor || item.value);

        // Crear o actualizar gráfico
        if (window.evolutionChart && typeof window.evolutionChart.destroy === 'function') {
            window.evolutionChart.destroy();
        }

        window.evolutionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'PBG (Millones)',
                    data: data,
                    borderColor: '#76B82A',
                    backgroundColor: 'rgba(118, 184, 42, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Evolución del PBG'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000000).toFixed(1) + 'B';
                            }
                        }
                    }
                }
            }
        });
        */
    }

    updateElement(selector, content) {
        const element = document.querySelector(selector);
        if (element) {
            element.textContent = content;
        }
    }

    showLoader() {
        const loader = document.getElementById('loading-backdrop');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    hideLoader() {
        const loader = document.getElementById('loading-backdrop');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    showError(message) {
        console.error(message);
        // Mostrar error en la UI
        const errorContainer = document.getElementById('error-container');
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <strong>Error:</strong> ${message}
                </div>
            `;
            errorContainer.style.display = 'block';
        }
    }

    // Método para recargar datos
    async refresh() {
        this.cache.clear();
        await this.loadDashboardData();
    }

    // Método para obtener datos de un sector específico
    async getSectorData(sectorCode) {
        return await this.fetchAPI(`/sector/${sectorCode}`);
    }
}

// Función global para inicializar el dashboard
window.initDashboard = function(apiBaseUrl) {
    window.dashboardAPI = new DashboardAPI(apiBaseUrl);
};

// Auto-inicializar si se proporciona la URL de la API
document.addEventListener('DOMContentLoaded', function() {
    if (window.API_BASE_URL) {
        try {
            window.initDashboard(window.API_BASE_URL);
            // Cargar datos iniciales
            window.dashboardAPI.loadDashboardData().catch(error => {
                console.error('Error cargando datos del dashboard:', error);
                window.dashboardAPI.showError('No se pudieron cargar los datos del dashboard');
            });
        } catch (error) {
            console.error('Error inicializando dashboard:', error);
        }
    } else {
        console.warn('API_BASE_URL no está definida');
    }
});