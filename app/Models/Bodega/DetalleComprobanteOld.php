<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class DetalleComprobanteOld extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.detalle_comprobante_old';
    protected $primaryKey  = 'iddetalle_comprobante_old';
    public $timestamps = false;

}
?>