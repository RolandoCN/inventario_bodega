<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class FarmLaboratorio extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.farm_laboratorio';
    protected $primaryKey  = 'idfarm_lab';
    public $timestamps = false;

}
?>