<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'esq_pacientes.pacientes';
    protected $primaryKey  = 'id_paciente';
    public $timestamps = false;

}
?>