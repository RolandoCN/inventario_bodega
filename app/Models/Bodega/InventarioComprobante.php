<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class InventarioComprobante extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'inventario.comprobante';
    protected $primaryKey  = 'idcomprobante';
    public $timestamps = false;

}
?>