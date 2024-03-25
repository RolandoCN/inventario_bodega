<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class PaqueteCirugia extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.paquete_cirugia';
    protected $primaryKey  = 'idpaquete_cirugia';
    public $timestamps = false;

}
?>