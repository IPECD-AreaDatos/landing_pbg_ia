<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PbgResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'year' => $this->{'Año'},
            'trimester' => $this->{'Trimestre'},
            'activity_code' => $this->{'Actividad'},
            'activity_description' => $this->{'Actividad_desc'},
            'value' => (float) $this->{'Valor'},
            'formatted_value' => number_format($this->{'Valor'}, 2, ',', '.'),
            'qoq_variation' => $this->{'Variacion QoQ (%)'} ? round($this->{'Variacion QoQ (%)'}, 2) : null,
            'yoy_variation' => $this->{'Variacion YoY (%)'} ? round($this->{'Variacion YoY (%)'}, 2) : null,
            'value_millions' => round($this->{'Valor'} / 1000000, 2), // Convertir a millones
        ];
    }
}
