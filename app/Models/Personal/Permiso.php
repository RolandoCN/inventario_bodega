<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $connection = 'mysql';
    protected $table = 'per_permiso';
    protected $primaryKey  = 'id_permiso';
    public $timestamps = false;


}
?>