<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.insumo';
    protected $primaryKey  = 'codinsumo';
    public $timestamps = false;

}
?>