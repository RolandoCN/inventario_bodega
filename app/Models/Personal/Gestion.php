<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Gestion extends Model
{
    // protected $connection = 'mysql';
    // protected $table = 'per_gestion';
    // protected $primaryKey  = 'id_gestion';
    // public $timestamps = false;

    protected $connection = 'pgsql';
    protected $table = 'bodega.per_gestion';
    protected $primaryKey  = 'id_gestion';
    public $timestamps = false;

}
?>