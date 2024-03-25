<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class EntregaPaqueteCqCo extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.entrega_paquete_co_cq';
    protected $primaryKey  = 'identrega_paquete_co_cq';
    public $timestamps = false;

    public function paquete(){
        return $this->belongsTo('App\Models\Bodega\Paquete', 'idpaquete', 'id_paquete');
    }
}
?>