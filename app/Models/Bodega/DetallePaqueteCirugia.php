<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class DetallePaqueteCirugia extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.detalle_paquetes_cirugia';
    protected $primaryKey  = 'id_detalle_paquetes_cirugia';
    public $timestamps = false;

    public function medicamento(){
        return $this->belongsTo('App\Models\Bodega\Medicamento', 'id_item', 'coditem');
    }

    public function insumo(){
        return $this->belongsTo('App\Models\Bodega\Insumo', 'id_item', 'codinsumo');
    }

}
?>