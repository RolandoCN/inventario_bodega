<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class InsumoAreaEspecialidad extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.insumo_area_especialidad';
    protected $primaryKey  = 'idinsumo_area_especialidad';
    public $timestamps = false;

}
?>