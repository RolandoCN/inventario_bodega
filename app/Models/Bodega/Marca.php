<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $connection = 'mysql';
    protected $table = 'marca';
    protected $primaryKey  = 'idmarca';
    public $timestamps = false;

    
}
?>