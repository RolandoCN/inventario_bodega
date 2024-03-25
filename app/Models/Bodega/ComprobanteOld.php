<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class ComprobanteOld extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.comprobante_old';
    protected $primaryKey  = 'idcomprobante_old';
    public $timestamps = false;

}
?>