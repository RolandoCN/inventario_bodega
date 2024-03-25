<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'inventario.tipocomprobante';
    protected $primaryKey  = 'idtipocomprobante';
    public $timestamps = false;

}
?>