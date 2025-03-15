<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\AuditModelTrait;

class ParteClasificacion extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AuditModelTrait;

    protected $table = 'parte_clasificaciones';

    protected $fillable = ['parte_id', 'clasificacion_id', 'created_by', 'last_modified_by', 'deleted_by'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'deleted_at' => 'datetime:d/m/Y H:i:s',
    ];
}
