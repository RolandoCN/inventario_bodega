<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Proteccion extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.proteccion';
    protected $primaryKey  = 'id';
    public $timestamps = false;

}
?>