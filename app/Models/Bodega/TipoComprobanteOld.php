<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class TipoComprobanteOld extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.tipo_comprobante_old';
    protected $primaryKey  = 'idtipocom';
    public $timestamps = false;

}
?>