<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class KardexDetDialisis extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'esq_dialisis.kardex_detalle';
    protected $primaryKey  = 'id_registro';
    public $timestamps = false;

}
?>