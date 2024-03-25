<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class PedidoBodegaGral extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.pedido_bod_gral';
    protected $primaryKey  = 'idpedido_bod_gral';
    public $timestamps = false;

    
    public function detalle(){
        return $this->belongsTo('App\Models\Bodega\DetalleComprobante', 'iddetallecomprobante', 'iddetalle_comprobante')->with('comprobante');
    }

    public function paquete(){
        return $this->belongsTo('App\Models\Bodega\Paquete', 'id_paquete', 'id_paquete');
    }

}
?>