<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class DetallePaquete extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.detalle_paquetes';
    protected $primaryKey  = 'iddetalle_paq';
    public $timestamps = false;

    public function medicamento(){
        return $this->belongsTo('App\Models\Bodega\Medicamento', 'id_item', 'coditem');
    }

    public function insumo(){
        return $this->belongsTo('App\Models\Bodega\Insumo', 'id_item', 'codinsumo');
    }

}
?>