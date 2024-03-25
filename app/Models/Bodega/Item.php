<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.items';
    protected $primaryKey  = 'codi_it';
    public $timestamps = false;

}
?>