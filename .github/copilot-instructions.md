# PBG Dashboard - Laravel Economic Data Platform

## Project Overview
Laravel 11 dashboard for migrating Next.js PBG (Producto Bruto Geográfico) economic data visualization. Connects to remote MySQL database `datalake_economico` and serves economic indicators through API and dashboard views.

## Architecture & Key Components

### Data Layer
- **Model**: `PbgData` maps to `pbg_anual_desglosado` table (no timestamps)
- **Service**: `PbgService` handles business logic and data aggregation
- **Database**: Remote MySQL connection via `datalake_economico`
- **Key Fields**: `letra` (sector code), `descripcion`, `año`, `valor`, `variacion_interanual`

### API Structure
All API routes under `/api/pbg/` prefix:
- `GET /charts` - Aggregated chart data for dashboard
- `GET /latest` - Latest year sector data
- `GET /years` - Available years list
- `GET /sectors` - All sectors
- `GET /sector/{codigo}` - Specific sector data

### Sector Hierarchy
- **Main sectors**: Single letter codes (A-P)
- **Subsectors**: Multi-character codes (A01, A02, etc.)
- **PBG**: Special total aggregate identified by `letra = 'PBG'`
- Use `byMainSectors()` scope to filter main sectors only

## Development Patterns

### Model Scopes Pattern
```php
// Use existing scopes in PbgData model
$data = PbgData::byYear(2023)
    ->byMainSectors()
    ->orderBy('letra')
    ->get();
```

### Service Layer Pattern
- Inject `PbgService` via constructor DI
- Business logic stays in service, not controllers
- Example: `$this->pbgService->getChartData()`

### API Resource Pattern
- Use `PbgResource` for single items
- Use `PbgCollection` for collections
- Return consistent JSON structure with `message` and `data`

## Deployment & Environment

### Vercel Configuration
- Entry point: `api/index.php`
- Laravel bootstrap adapted for serverless
- Environment variables use `@variable_name` format in `vercel.json`
- Static assets cached for 1 year

### Database Connection
- Default: SQLite for local development
- Production: MySQL via environment variables
- No migrations - connects to existing `pbg_anual_desglosado` table

## Frontend Integration
- Blade templates in `resources/views/`
- Dashboard uses vanilla JS with fetch API
- Chart.js for data visualization
- Bootstrap 5 for responsive design
- Components: charts, indicators, sectors, evolution

## Key Commands
```bash
# Local development
php artisan serve

# List API routes
php artisan list:api-routes

# Check database connection
php artisan tinker
>>> PbgData::count()
```

## Common Tasks
- **Adding new API endpoint**: Update `routes/api.php` and `PbgController`
- **New data aggregation**: Add method to `PbgService`
- **Chart modifications**: Update `dashboard.blade.php` and API response
- **Database queries**: Use model scopes for consistency