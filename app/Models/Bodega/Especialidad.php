<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'esq_catalogos.especialidad';
    protected $primaryKey  = 'id_especialidad';
    public $timestamps = false;

}
?>