<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class UsuarioPerfil extends Model
{
   
    protected $connection = 'mysql';
    protected $table = 'perfil_usuario';
    protected $primaryKey  = 'idperfilusuario';
    public $timestamps = false;

    public function nombre_perfil(){
        return $this->belongsTo('App\Models\Personal\Perfil', 'idperfil', 'idperfil')->where('estado', 'A');
    }
}
?>