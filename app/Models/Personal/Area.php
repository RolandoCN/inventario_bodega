<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.area';
    protected $primaryKey  = 'id_area';
    public $timestamps = false;

}
?>