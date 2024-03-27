<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $connection = 'mysql';
    protected $table = 'modelo';
    protected $primaryKey  = 'idmodelo';
    public $timestamps = false;

    
}
?>