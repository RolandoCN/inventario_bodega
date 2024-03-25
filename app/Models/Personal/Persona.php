<?php

namespace App\Models\Personal;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $connection = 'mysql';
    protected $table = 'persona';
    protected $primaryKey  = 'idpersona';
    public $timestamps = false;

}
?>