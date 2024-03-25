<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class DetalleComprobante extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.detalle_comprobante';
    protected $primaryKey  = 'iddetalle_comprobante';
    public $timestamps = false;

    public function item(){
        return $this->belongsTo('App\Models\Bodega\Medicamento', 'id_item', 'coditem');
    }

    public function insumo(){
        return $this->belongsTo('App\Models\Bodega\Insumo', 'id_item', 'codinsumo');
    }

    public function laboratorio(){
        return $this->belongsTo('App\Models\Bodega\Laboratorio', 'id_item', 'id');
    }

    public function itemlab(){
        return $this->belongsTo('App\Models\Bodega\Item', 'id_item', 'codi_it');
    }

    public function itemproteccion(){
        return $this->belongsTo('App\Models\Bodega\Proteccion', 'id_item', 'id');
    }


    public function pedido(){
        return $this->belongsTo('App\Models\Bodega\PedidoBodegaGral', 'iddetalle_comprobante', 'iddetallecomprobante')->with('paquete');
    }

    public function existencia(){
        return $this->belongsTo('App\Models\Bodega\Existencia', 'iddetalle_comprobante', 'iddetalle_comprobante');
    }

    public function comprobante(){
        return $this->belongsTo('App\Models\Bodega\Comprobante', 'idcomprobante', 'idcomprobante')->with('areaPer','nomarea');
    }

    public function prodbod(){
        return $this->belongsTo('App\Models\Bodega\ProductoBodega', 'idbodprod', 'idbodprod')->with('lote');
    }

    

}
?>