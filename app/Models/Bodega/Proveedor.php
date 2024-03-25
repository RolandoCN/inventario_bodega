<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.proveedor';
    protected $primaryKey  = 'idprov';
    public $timestamps = false;

}
?>