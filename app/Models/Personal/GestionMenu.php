<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class GestionMenu extends Model
{
    // protected $connection = 'mysql';
    // protected $table = 'per_gestion_menu';
    // protected $primaryKey  = 'id_gestion_menu';
    // public $timestamps = false;

    protected $connection = 'pgsql';
    protected $table = 'bodega.per_gestion_menu';
    protected $primaryKey  = 'id_gestion_menu';
    public $timestamps = false;

    public function gestion(){
        return $this->belongsTo('App\Models\Personal\Gestion', 'id_gestion', 'id_gestion');
    }


    public function menu(){
        return $this->belongsTo('App\Models\Personal\Menu', 'id_menu', 'id_menu')->where('estado','A');
    }

}
?>