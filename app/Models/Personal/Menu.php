<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    // protected $connection = 'mysql';
    // protected $table = 'per_menu';
    // protected $primaryKey  = 'id_menu';
    // public $timestamps = false;

    protected $connection = 'pgsql';
    protected $table = 'bodega.per_menu';
    protected $primaryKey  = 'id_menu';
    public $timestamps = false;

}
?>