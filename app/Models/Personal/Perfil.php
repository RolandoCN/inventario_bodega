<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $connection = 'mysql';
    protected $table = 'perfil';
    protected $primaryKey  = 'idperfil';
    public $timestamps = false;


}
?>