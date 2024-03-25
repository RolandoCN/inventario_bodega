<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class MedicamentoAreaEspecialidad extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.medicina_area_especialidad';
    protected $primaryKey  = 'idmedicina_area_especialidad';
    public $timestamps = false;

}
?>