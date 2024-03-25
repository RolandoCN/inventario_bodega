<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.comprobante';
    protected $primaryKey  = 'idcomprobante';
    public $timestamps = false;

    
    public function detalle(){
        return $this->hasMany('App\Models\Bodega\DetalleComprobante', 'idcomprobante', 'idcomprobante')
        ->with('item','pedido','existencia','prodbod'); 
    }

    public function detalle_pedido(){
        return $this->hasMany('App\Models\Bodega\DetallePedido', 'idcomprobante', 'idcomprobante')
        ->with('item','insumo','laboratorio','itemlab','itemproteccion'); 
    }

    public function detalle_insumo(){
        return $this->hasMany('App\Models\Bodega\DetalleComprobante', 'idcomprobante', 'idcomprobante')
        ->with('insumo','pedido','existencia','prodbod');
    }

    public function detalle_item(){
        return $this->hasMany('App\Models\Bodega\DetalleComprobante', 'idcomprobante', 'idcomprobante')
        ->with('itemlab','pedido','existencia');
    }

    public function detalle_proteccion(){
        return $this->hasMany('App\Models\Bodega\DetalleComprobante', 'idcomprobante', 'idcomprobante')
        ->with('itemproteccion','pedido','existencia');
    }

    public function entregado(){
        return $this->belongsTo('App\Models\User', 'id_usuario_ingresa', 'id')->with('persona');
    }

    public function responsable(){
        return $this->belongsTo('App\Models\User', 'id_usuario_aprueba', 'id')->with('persona');
    }

    public function recibido(){
        return $this->belongsTo('App\Models\User', 'id_usuario_aprueba', 'id')->with('persona');
    }

    public function bodega(){
        return $this->belongsTo('App\Models\Bodega\Bodega', 'idbodega', 'idbodega');
    }

    public function tipo(){
        return $this->belongsTo('App\Models\Bodega\TipoComprobanteOld', 'idtipo_comprobante', 'idtipocom');
    }

    public function tipoIngreso(){
        return $this->belongsTo('App\Models\Bodega\TipoIngreso', 'tipo', 'idtipo_ingreso');
    }

    public function areaPer(){
        return $this->belongsTo('App\Models\Personal\Perfil', 'area', 'id_perfil');
    }

    public function proveedor(){
        return $this->belongsTo('App\Models\Bodega\Proveedor', 'id_proveedor', 'idprov');
    }

    public function devolucion(){
        return $this->belongsTo('App\Models\Personal\Funcionario', 'iduser_devuelve', 'idper');
    }

    public function nomarea(){
        return $this->belongsTo('App\Models\Personal\Area', 'area', 'id_area');
    }
    
    public function detalle_lab(){
        return $this->hasMany('App\Models\Bodega\DetalleComprobante', 'idcomprobante', 'idcomprobante')
        ->with('laboratorio','pedido','existencia');
    }

    public function paciente(){
        return $this->belongsTo('App\Models\Bodega\Paciente', 'id_paciente', 'id_paciente');
    }

    public function cie(){
        return $this->belongsTo('App\Models\Bodega\Cie', 'id_cie10', 'cie10_id');
    }

    public function especialidad(){
        return $this->belongsTo('App\Models\Bodega\Especialidad', 'id_especialidad', 'id_especialidad');
    }

   


}
?>