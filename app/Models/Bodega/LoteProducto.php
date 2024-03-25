<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class LoteProducto extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.lotexprod';
    protected $primaryKey  = 'idlote';
    public $timestamps = false;

}
?>