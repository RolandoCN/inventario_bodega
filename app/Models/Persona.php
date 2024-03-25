<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $connection = 'mysql';
    protected $table = 'persona';
    protected $primaryKey  = 'idpersona';
    public $timestamps = false;

}
?>