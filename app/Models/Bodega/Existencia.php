<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Existencia extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.existencia';
    protected $primaryKey  = 'idexistencia';
    public $timestamps = false;

    public function solicita(){
        return $this->belongsTo('App\Models\User', 'idusuario_solicita', 'id')->with('persona');
    }

    public function detalle(){
        return $this->belongsTo('App\Models\Bodega\DetalleComprobante', 'iddetalle_comprobante', 'iddetalle_comprobante')->with('comprobante');
    }

    public function prodbod(){
        return $this->belongsTo('App\Models\Bodega\ProductoBodega', 'idbodprod', 'idbodprod')
        ->with('medicamento','insumo','laboratorio','itemproteccion','items');
    }

    public function prodbod2(){
        return $this->belongsTo('App\Models\Bodega\ProductoBodega', 'idbodprod', 'idbodprod')
        ->with('medicamento','insumo','laboratorio','itemproteccion','items');
    }

    

}
?>