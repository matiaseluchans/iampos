<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\AuditModelTrait;

class Parte extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AuditModelTrait;

    protected $fillable = ['estructura_organica_id', 'estructura_organica', 'numero', 'numero_unidad', 'anio', 'completado_por', 'fecha_informe', 'fecha_hecho',  'relato', 'observaciones', 'estado_id', 'created_by', 'last_modified_by', 'deleted_by'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'deleted_at' => 'datetime:d/m/Y H:i:s',
    ];

    public static function getNumeroParte()
    {
        // Obtener el año actual
        $anio = now()->year;

        // Buscar el último registro del mismo año y obtener el número de secuencia
        $parte = self::where('anio', $anio)
                              ->orderBy('numero', 'desc')
                              ->first();

        // Si no hay registros para este año, la secuencia comienza en 1
        $numero = $parte ? $parte->numero + 1 : 1;

        return $numero;
    }

    public static function getNumeroUnidadParte($unidad)
    {
        // Obtener el año actual
        $anio = now()->year;
        // Buscar el último registro del mismo año y obtener el número de secuencia
        $parte = self::where('estructura_organica', $unidad)
                              ->orderBy('numero', 'desc')
                              ->first();

        // Si no hay registros para este año, la secuencia comienza en 1
        $numero = $parte ? $parte->numero + 1 : 1;

        return $numero;
    }

    public function setRelato($value)
    {        
        $this->attributes['relato'] = strtoupper($value);
    }

    public function setObservaciones($value)
    {
        $this->attributes['observaciones'] = strtoupper($value);
    }

    public function setEstructuraOrganica($value)
    {
        $this->attributes['estructura_organica'] = strtoupper($value);
    }
    
}
