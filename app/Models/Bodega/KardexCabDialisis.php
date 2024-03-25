<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class KardexCabDialisis extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'esq_dialisis.kardex';
    protected $primaryKey  = 'id_registro';
    public $timestamps = false;

}
?>