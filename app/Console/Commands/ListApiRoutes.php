<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class ListApiRoutes extends Command
{
    protected $signature = 'api:list';
    protected $description = 'List available PBG statistics APIs (read-only)';

    public function handle()
    {
        $this->info("� Dashboard PBG - APIs de Estadísticas");
        $this->info("=" . str_repeat("=", 45));
        
        $this->info("\n🎯 APIS DISPONIBLES (Solo Lectura):");
        $this->line("  GET  /api/pbg/charts             - Datos para gráficos del dashboard");
        $this->line("  GET  /api/pbg/latest             - Datos más recientes disponibles");
        $this->line("  GET  /api/pbg/years              - Lista de años con datos");
        $this->line("  GET  /api/pbg/sectors            - Lista de sectores económicos");
        $this->line("  GET  /api/pbg/sector/{codigo}    - Datos históricos por sector");
        
        $this->info("\n� EJEMPLOS DE USO:");
        $this->line("  curl http://localhost:8000/api/pbg/years");
        $this->line("  curl http://localhost:8000/api/pbg/sectors");
        $this->line("  curl http://localhost:8000/api/pbg/sector/G");
        $this->line("  curl http://localhost:8000/api/pbg/charts");
        $this->line("  curl http://localhost:8000/api/pbg/latest");
        
        $this->info("\n🏭 CÓDIGOS DE SECTORES PRINCIPALES:");
        $this->line("  A - Agricultura, ganadería y silvicultura");
        $this->line("  G - Comercio mayorista y minorista");
        $this->line("  K - Actividades inmobiliarias y empresariales");
        $this->line("  D - Industria manufacturera");
        $this->line("  L - Administración gubernamental");
        $this->line("  E - Electricidad, gas y agua");
        
        $this->info("\n� DATOS DISPONIBLES:");
        $this->line("  • Período: 2004-2023");
        $this->line("  • Valores en millones de pesos constantes de 2004");
        $this->line("  • Variación anual (YoY) incluida");
        $this->line("  • 16 sectores económicos principales");
        
        $this->info("\n🗄️ FUENTE DE DATOS:");
        $this->line("  Vista: v_pbg_anual_by_act");
        $this->line("  Base: datalake_economico (MySQL remoto)");
        
        $this->info("\n✅ Total APIs: 5 (Solo lectura)");
        $this->info("🌐 Dashboard: http://127.0.0.1:8000");
        $this->info("🔗 Base URL: http://127.0.0.1:8000/api");
    }
}