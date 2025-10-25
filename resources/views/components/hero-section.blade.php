<section class="hero-section">
    <div class="container">
        <h1 class="hero-title">
            Producto Bruto<br>
            Geográfico de <span class="hero-subtitle">Corrientes</span>
        </h1>
        <p class="hero-description">
            Análisis oficial del <strong>Producto Bruto Geográfico (PBG) de Corrientes</strong>, correspondiente al período
            {{ $statistics['min_year'] }}–{{ $statistics['max_year'] }}.
            Datos elaborados por el <strong>Instituto Provincial de Estadística y Ciencia de Datos de Corrientes</strong>,
            expresados en precios constantes de 2004.<br>
            <strong>El conjunto abarca {{ $statistics['sectors_count'] }} categorías económicas principales</strong> 
            (16 sectores productivos + PBG total) con mas de 35 fuentes de información diferentes y
            <strong>{{ number_format($statistics['total_records']) }} registros históricos</strong>, 
            lo que permite un análisis detallado del desarrollo económico provincial.
        </p>
    </div>

</section>