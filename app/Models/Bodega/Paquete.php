<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.paquetes';
    protected $primaryKey  = 'id_paquete';
    public $timestamps = false;

}
?>