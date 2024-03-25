<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.bodega';
    protected $primaryKey  = 'idbodega';
    public $timestamps = false;

}
?>