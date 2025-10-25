<?php

namespace App\Services;

use App\Models\PbgData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PbgService
{
    public function __construct(
        private PbgData $pbgModel
    ) {}

    /**
     * Obtener todos los datos PBG con filtros opcionales
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->pbgModel->newQuery();

        if (isset($filters['year'])) {
            $query->where('año', $filters['year']);
        }

        if (isset($filters['sector'])) {
            if (strlen($filters['sector']) === 1) {
                // Si es sector principal, buscar todos los subsectores
                $query->where('letra', 'like', $filters['sector'] . '%');
            } else {
                // Si es subsector específico
                $query->where('letra', $filters['sector']);
            }
        }

        if (isset($filters['description'])) {
            $query->where('descripcion', 'like', '%' . $filters['description'] . '%');
        }

        if (isset($filters['year_from'])) {
            $query->where('año', '>=', $filters['year_from']);
        }

        if (isset($filters['year_to'])) {
            $query->where('año', '<=', $filters['year_to']);
        }

        if (isset($filters['main_sectors_only']) && $filters['main_sectors_only']) {
            $query->byMainSectors();
        }

        return $query->orderBy('año', 'desc')
                    ->orderBy('letra', 'asc')
                    ->get();
    }

    /**
     * Obtener datos paginados
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->pbgModel->newQuery();

        if (isset($filters['year'])) {
            $query->where('año', $filters['year']);
        }

        if (isset($filters['sector'])) {
            if (strlen($filters['sector']) === 1) {
                $query->where('letra', 'like', $filters['sector'] . '%');
            } else {
                $query->where('letra', $filters['sector']);
            }
        }

        if (isset($filters['description'])) {
            $query->where('descripcion', 'like', '%' . $filters['description'] . '%');
        }

        if (isset($filters['main_sectors_only']) && $filters['main_sectors_only']) {
            $query->byMainSectors();
        }

        return $query->orderBy('año', 'desc')
                    ->orderBy('letra', 'asc')
                    ->paginate($perPage);
    }

    /**
     * Crear nuevo registro PBG
     */
    public function create(array $data): PbgData
    {
        return $this->pbgModel->create($data);
    }

    /**
     * Actualizar registro PBG
     */
    public function update(int $id, array $data): PbgData
    {
        $pbgData = $this->pbgModel->findOrFail($id);
        $pbgData->update($data);
        return $pbgData->fresh();
    }

    /**
     * Eliminar registro PBG
     */
    public function delete(int $id): bool
    {
        return $this->pbgModel->findOrFail($id)->delete();
    }

    /**
     * Obtener estadísticas del PBG
     */
    public function getStatistics(): array
    {
        $data = $this->pbgModel->all();

        return [
            'total_records' => $data->count(),
            'years_available' => $data->pluck('año')->unique()->sort()->values(),
            'sectors_available' => $data->pluck('letra')->unique()->sort()->values(),
            'main_sectors_count' => $data->where('letra', 'length', 1)->pluck('letra')->unique()->count(),
            'total_value' => $data->sum('valor'),
            'average_value' => $data->avg('valor'),
            'max_value' => $data->max('valor'),
            'min_value' => $data->min('valor'),
            'by_year' => $data->groupBy('año')->map(function ($items, $year) {
                return [
                    'year' => $year,
                    'total_value' => $items->sum('valor'),
                    'sectors_count' => $items->count(),
                ];
            })->values(),
            'by_sector' => $data->groupBy('letra')->map(function ($items, $sector) {
                return [
                    'sector' => $sector,
                    'description' => $items->first()->descripcion,
                    'total_value' => $items->sum('valor'),
                    'years_count' => $items->pluck('año')->unique()->count(),
                    'is_main_sector' => strlen($sector) === 1 || $sector === 'PBG',
                    'is_total' => $sector === 'PBG'
                ];
            })->values(),
        ];
    }

    /**
     * Obtener datos para gráficos
     */
    public function getChartData(): array
    {
        // Obtener solo sectores principales para los gráficos principales
        $mainSectorsData = $this->pbgModel->byMainSectors()->get();
        $allData = $this->pbgModel->all();

        return [
            'evolution_by_year' => $mainSectorsData->groupBy('año')->map(function ($items, $year) {
                return [
                    'year' => (int) $year,
                    'total' => (float) $items->sum('valor'),
                ];
            })->sortBy('year')->values(),
            
            'by_sector' => $mainSectorsData->groupBy('letra')->map(function ($items, $sector) {
                $total = (float) $items->sum('valor');
                return [
                    'sector' => $sector,
                    'description' => $items->first()->descripcion,
                    'total' => $total,
                    'percentage' => 0, // Se calculará en el frontend
                ];
            })->sortByDesc('total')->values(),
            
            'sector_evolution' => $mainSectorsData->groupBy('letra')->map(function ($items, $sector) {
                return [
                    'sector' => $sector,
                    'description' => $items->first()->descripcion,
                    'data' => $items->groupBy('año')->map(function ($yearItems, $year) {
                        return [
                            'year' => (int) $year,
                            'value' => (float) $yearItems->sum('valor'),
                            'yoy_variation' => $yearItems->first()->variacion_interanual ? 
                                (float) $yearItems->first()->variacion_interanual : null
                        ];
                    })->sortBy('year')->values(),
                ];
            })->sortBy('sector')->values(),

            // Datos adicionales para análisis detallado
            'latest_year_data' => $mainSectorsData->where('año', $mainSectorsData->max('año'))
                ->map(function ($item) {
                    return [
                        'sector' => $item->letra,
                        'description' => $item->descripcion,
                        'value' => (float) $item->valor,
                        'yoy_variation' => $item->variacion_interanual ? (float) $item->variacion_interanual : null
                    ];
                })->sortByDesc('value')->values(),

            'hierarchy_summary' => [
                'main_sectors' => $allData->where('letra', 'length', 1)->pluck('letra')->unique()->count(),
                'total_subsectors' => $allData->where('letra', 'length', '>', 1)->pluck('letra')->unique()->count(),
                'total_records' => $allData->count(),
                'year_range' => [
                    'from' => $allData->min('año'),
                    'to' => $allData->max('año')
                ]
            ]
        ];
    }
}