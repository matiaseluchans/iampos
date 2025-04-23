<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalPsa extends Model
{
    use HasFactory;

    //
    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 'V_PERSONAL_PSA';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'LEGAJO';
}
