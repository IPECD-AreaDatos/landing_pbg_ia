<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PbgCollection;
use App\Models\PbgData;
use App\Services\PbgService;
use Illuminate\Http\JsonResponse;

class PbgController extends Controller
{
    public function __construct(
        private PbgService $pbgService
    ) {
    }

    /**
     * Get chart data for dashboard
     * GET /api/pbg/charts
     */
    public function charts(): JsonResponse
    {
        try {
            $chartData = $this->pbgService->getChartData();

            return response()->json([
                'message' => 'Datos de gráficos obtenidos exitosamente',
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener datos de gráficos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest PBG data
     * GET /api/pbg/latest
     */
    public function latest(): JsonResponse
    {
        try {
            $latestYear = PbgData::max('año');
            $latestData = PbgData::where('año', $latestYear)
                ->byMainSectors()
                ->orderBy('letra')
                ->get();

            return response()->json([
                'message' => 'Últimos datos PBG obtenidos exitosamente',
                'data' => [
                    'year' => $latestYear,
                    'data' => $latestData,
                    'total' => $latestData->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener últimos datos PBG',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available years
     * GET /api/pbg/years
     */
    public function getYears(): JsonResponse
    {
        try {
            $years = PbgData::select('año')
                ->distinct()
                ->orderBy('año', 'desc')
                ->pluck('año');

            return response()->json([
                'message' => 'Años disponibles obtenidos exitosamente',
                'data' => [
                    'years' => $years,
                    'total' => $years->count(),
                    'range' => [
                        'from' => $years->min(),
                        'to' => $years->max()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener años disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all sectors (hierarchical structure)
     * GET /api/pbg/sectors
     */
    public function getSectors(): JsonResponse
    {
        try {
            // Obtener sectores principales
            $mainSectors = PbgData::select('letra', 'descripcion')
                ->byMainSectors()
                ->distinct()
                ->orderBy('letra')
                ->get()
                ->map(function ($item) {
                    return [
                        'code' => $item->letra,
                        'description' => $item->descripcion,
                        'level' => 1,
                        'parent' => null
                    ];
                });

            // Obtener subsectores organizados por sector principal
            $allSectors = collect($mainSectors);
            
            foreach ($mainSectors as $mainSector) {
                $subSectors = PbgData::select('letra', 'descripcion')
                    ->bySubSectors($mainSector['code'])
                    ->distinct()
                    ->orderBy('letra')
                    ->get()
                    ->map(function ($item) use ($mainSector) {
                        return [
                            'code' => $item->letra,
                            'description' => $item->descripcion,
                            'level' => strlen($item->letra),
                            'parent' => $mainSector['code']
                        ];
                    });
                
                $allSectors = $allSectors->merge($subSectors);
            }

            return response()->json([
                'message' => 'Sectores obtenidos exitosamente',
                'data' => [
                    'sectors' => $allSectors->values(),
                    'main_sectors' => $mainSectors,
                    'total' => $allSectors->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener sectores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data by sector (supports hierarchical codes)
     * GET /api/pbg/sector/{codigo}
     */
    public function bySector(string $codigo): JsonResponse
    {
        try {
            // Casos especiales
            if ($codigo === 'PBG') {
                // PBG es el total general
                $data = PbgData::bySector('PBG')
                    ->orderBy('año', 'desc')
                    ->get();
                
                if ($data->isEmpty()) {
                    return response()->json([
                        'message' => 'No se encontraron datos para el PBG',
                        'data' => []
                    ], 404);
                }
                
                $responseData = [
                    'sector_info' => [
                        'code' => 'PBG',
                        'description' => 'Producto Bruto Geográfico',
                        'is_main_sector' => true,
                        'is_total' => true
                    ],
                    'data' => $data->map(function ($item) {
                        return [
                            'year' => $item->año,
                            'value' => (float) $item->valor,
                            'yoy_variation' => $item->variacion_interanual ? (float) $item->variacion_interanual : null
                        ];
                    }),
                    'total_records' => $data->count()
                ];
            } else {
                // Verificar si es un sector principal (A-P) o subsector
                $isMainSector = strlen($codigo) === 1;
                
                if ($isMainSector) {
                    // Si es sector principal, incluir todos sus subsectores
                    $data = PbgData::where('letra', 'like', $codigo . '%')
                        ->where('letra', '!=', 'PBG') // Excluir PBG
                        ->orderBy('año', 'desc')
                        ->orderBy('letra', 'asc')
                        ->get();
                    
                    // Agrupar por subsector
                    $grouped = $data->groupBy('letra')->map(function ($sectorData, $sectorCode) {
                        return [
                            'sector' => $sectorCode,
                            'description' => $sectorData->first()->descripcion,
                            'level' => strlen($sectorCode),
                            'data' => $sectorData->map(function ($item) {
                                return [
                                    'year' => $item->año,
                                    'value' => (float) $item->valor,
                                    'yoy_variation' => $item->variacion_interanual ? (float) $item->variacion_interanual : null
                                ];
                            })->sortByDesc('year')->values()
                        ];
                    })->values();
                    
                    $responseData = [
                        'sector_info' => [
                            'code' => $codigo,
                            'description' => $data->where('letra', $codigo)->first()->descripcion ?? 'Sector no encontrado',
                            'is_main_sector' => true
                        ],
                        'subsectors' => $grouped,
                        'total_records' => $data->count()
                    ];
                } else {
                    // Si es subsector específico
                    $data = PbgData::bySector($codigo)
                        ->orderBy('año', 'desc')
                        ->get();
                    
                    if ($data->isEmpty()) {
                        return response()->json([
                            'message' => 'No se encontraron datos para el sector especificado',
                            'data' => []
                        ], 404);
                    }
                    
                    $responseData = [
                        'sector_info' => [
                            'code' => $codigo,
                            'description' => $data->first()->descripcion,
                            'is_main_sector' => false,
                            'parent_sector' => substr($codigo, 0, 1)
                        ],
                        'data' => $data->map(function ($item) {
                            return [
                                'year' => $item->año,
                                'value' => (float) $item->valor,
                                'yoy_variation' => $item->variacion_interanual ? (float) $item->variacion_interanual : null
                            ];
                        }),
                        'total_records' => $data->count()
                    ];
                }
            }

            return response()->json([
                'message' => "Datos del sector {$codigo} obtenidos exitosamente",
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener datos del sector',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}