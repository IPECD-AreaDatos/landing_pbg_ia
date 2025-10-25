<footer class="footer-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="footer-title">Gobierno de Corrientes</h3>
                <p class="footer-description">
                    Dashboard de estadísticas del Producto Bruto Geográfico (PBG) de la provincia de Corrientes.<br>
                    Datos oficiales {{ $statistics['min_year'] ?? '2004' }}-{{ $statistics['max_year'] ?? '2023' }} a precios constantes de 2004.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="https://estadistica.corrientes.gob.ar" 
                   target="_blank" 
                   class="btn btn-outline-light btn-lg">
                    <i class="fas fa-external-link-alt me-2"></i>
                    Ver más estadísticas
                </a>
            </div>
        </div>
        
        <div class="footer-copyright mt-3">
            © 2025 Gobierno de la Provincia de Corrientes. Todos los derechos reservados.
        </div>
    </div>
</footer>