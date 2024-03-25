<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class BodegaUsuario extends Model
{
    // protected $connection = 'mysql';
    // protected $table = 'per_perfil_acceso';
    // protected $primaryKey  = 'id_perfil_acceso';
    // public $timestamps = false;

    protected $connection = 'pgsql';
    protected $table = 'bodega.bodega_usuario';
    protected $primaryKey  = 'idbodega_usuario';
    public $timestamps = false;

    public function bodega(){
        return $this->belongsTo('App\Models\Bodega\Bodega', 'idbodega', 'idbodega');
    }
}
?>