<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="/">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <span class="brand-text">
                Gobierno de Corrientes
                <small class="text-muted d-block d-lg-inline ms-lg-2">Dashboard PBG</small>
            </span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-muted">
                    <i class="fas fa-database"></i> 
                    <span class="d-none d-md-inline">Estadísticas Económicas</span>
                    <span class="d-md-none">Stats</span>
                    {{ $statistics['min_year'] ?? '2004' }}-{{ $statistics['max_year'] ?? '2023' }}
                </span>
            </div>
        </div>
    </div>
</nav>