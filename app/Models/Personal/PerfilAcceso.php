<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class PerfilAcceso extends Model
{
    // protected $connection = 'mysql';
    // protected $table = 'per_perfil_acceso';
    // protected $primaryKey  = 'id_perfil_acceso';
    // public $timestamps = false;

    protected $connection = 'pgsql';
    protected $table = 'bodega.per_perfil_acceso';
    protected $primaryKey  = 'id_perfil_acceso';
    public $timestamps = false;

    public function menu(){
        return $this->belongsTo('App\Models\Personal\Menu', 'id_menu', 'id_menu');
    }
}
?>