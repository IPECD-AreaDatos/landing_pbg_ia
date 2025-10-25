@extends('layouts.app')

@section('title', 'PBG Corrientes - Dashboard Económico')

@push('styles')
<style>
    .loading-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sector-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: 1px solid #dee2e6;
    }

    .sector-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border-color: #76B82A;
    }

    .sector-card.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .subsectors-container {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chart-container {
        transition: opacity 0.3s ease-in-out;
    }

    .form-select-lg {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    .subsectors-loader {
        padding: 1rem;
        background: rgba(0, 123, 255, 0.05);
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .sector-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #76B82A;
    }

    .sector-variation {
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')

    <!-- Loading Backdrop -->
    <div id="loading-backdrop" class="loading-backdrop">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <div class="mt-3 text-white">
                <strong>Cargando datos del PBG...</strong>
            </div>
        </div>
    </div>

    <!-- Error Container -->
    <div id="error-container" style="display: none;"></div>

    <!-- Hero Section -->
    @include('components.hero-section')

    <!-- Key Indicators -->
    <!-- DEBUG: Incluir indicadores -->
    @include('components.indicators')

    <!-- Charts Section -->
    @include('components.charts')

    <!-- Sector Analysis -->
    @include('components.sectors')

    <!-- Interactive Evolution -->
    @include('components.evolution')
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dashboard API Manager -->
<script src="{{ asset('js/dashboard-api.js') }}"></script>

<script>
    // Configurar URL de la API
    window.API_BASE_URL = '{{ $api_base_url }}';
    
    // El dashboard se inicializará automáticamente cuando se cargue el DOM
    console.log('Dashboard configurado para usar API:', window.API_BASE_URL);
</script>
@endpush