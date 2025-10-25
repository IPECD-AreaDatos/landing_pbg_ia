<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PbgCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PbgResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'years' => $this->collection->pluck('Año')->unique()->sort()->values()->toArray(),
                'trimesters' => $this->collection->pluck('Trimestre')->unique()->sort()->values()->toArray(),
                'activities' => $this->collection->pluck('Actividad')->unique()->sort()->values()->toArray(),
                'activity_descriptions' => $this->collection->pluck('Actividad_desc')->unique()->take(10)->values()->toArray(),
                'total_value' => $this->collection->sum('Valor'),
                'total_value_millions' => round($this->collection->sum('Valor') / 1000000, 2),
                'latest_year' => $this->collection->max('Año'),
                'earliest_year' => $this->collection->min('Año'),
            ]
        ];
    }
}
