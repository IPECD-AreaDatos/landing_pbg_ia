<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PbgData extends Model
{
    use HasFactory;

    // Nombre de la tabla con datos desglosados jerárquicamente
    protected $table = 'pbg_anual_desglosado';
    
    // Esta tabla no tiene timestamps
    public $timestamps = false;

    // Campos de la tabla pbg_anual_desglosado
    protected $fillable = [
        'letra',
        'descripcion',
        'año',
        'valor',
        'variacion_interanual'
    ];

    protected $casts = [
        'año' => 'integer',
        'valor' => 'decimal:2',
        'variacion_interanual' => 'decimal:4'
    ];

    // Scopes para filtros comunes
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('año', $year);
    }

    public function scopeByYearRange(Builder $query, int $startYear, int $endYear): Builder
    {
        return $query->whereBetween('año', [$startYear, $endYear]);
    }

    public function scopeBySector(Builder $query, string $sector): Builder
    {
        return $query->where('letra', $sector);
    }

    public function scopeByMainSectors(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereRaw('CHAR_LENGTH(letra) = 1')
              ->orWhere('letra', 'PBG');
        });
    }

    public function scopeBySubSectors(Builder $query, string $mainSector): Builder
    {
        return $query->where('letra', 'like', $mainSector . '%')
                    ->whereRaw('CHAR_LENGTH(letra) > 1')
                    ->where('letra', '!=', 'PBG'); // Excluir PBG de subsectores
    }

    public function scopeByPbgTotal(Builder $query): Builder
    {
        return $query->where('letra', 'PBG');
    }

    public function scopeByDescription(Builder $query, string $description): Builder
    {
        return $query->where('descripcion', 'like', "%{$description}%");
    }

    public function scopeOrderByYear(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('año', $direction);
    }

    public function scopeOrderByValue(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('valor', $direction);
    }

    public function scopeOrderBySector(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('letra', $direction);
    }

    // Accessors para mantener compatibilidad con la API existente
    public function getYearAttribute()
    {
        return $this->attributes['año'] ?? null;
    }

    public function getSectorAttribute()
    {
        return $this->attributes['letra'] ?? null;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['descripcion'] ?? null;
    }

    public function getValueAttribute()
    {
        return $this->attributes['valor'] ?? null;
    }

    public function getYoyVariationAttribute()
    {
        return $this->attributes['variacion_interanual'] ?? null;
    }

    // Métodos helper para la estructura jerárquica
    public function isMainSector(): bool
    {
        return strlen($this->letra) === 1 || $this->letra === 'PBG';
    }

    public function getMainSector(): string
    {
        return substr($this->letra, 0, 1);
    }

    public function getHierarchyLevel(): int
    {
        return strlen($this->letra);
    }
}
