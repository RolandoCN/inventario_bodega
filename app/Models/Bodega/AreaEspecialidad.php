<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class AreaEspecialidad extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.area_especialidad';
    protected $primaryKey  = 'idarea_especialidad';
    public $timestamps = false;

}
?>