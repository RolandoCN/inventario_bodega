<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class MedicamentoEspecialidad extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'inventario.medicamento_especialidad';
    protected $primaryKey  = 'idmedic_especialidad';
    public $timestamps = false;

}
?>