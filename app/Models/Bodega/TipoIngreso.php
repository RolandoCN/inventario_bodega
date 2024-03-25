<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class TipoIngreso extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.tipo_ingreso';
    protected $primaryKey  = 'idtipo_ingreso';
    public $timestamps = false;

}
?>