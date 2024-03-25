<?php

namespace App\Models\Bodega;

use Illuminate\Database\Eloquent\Model;

class ProductoBodega extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'bodega.prodxbod';
    protected $primaryKey  = 'idbodprod';
    public $timestamps = false;

    public function medicamento(){
        return $this->belongsTo('App\Models\Bodega\Medicamento', 'idprod', 'coditem');
    }

    public function insumo(){
        return $this->belongsTo('App\Models\Bodega\Insumo', 'idprod', 'codinsumo');
    }

    public function laboratorio(){
        return $this->belongsTo('App\Models\Bodega\Laboratorio', 'idprod', 'id');
    }

    public function itemproteccion(){
        return $this->belongsTo('App\Models\Bodega\Proteccion', 'idprod', 'id');
    }

    public function items(){
        return $this->belongsTo('App\Models\Bodega\Item', 'idprod', 'codi_it');
    }

    public function lote(){
        return $this->belongsTo('App\Models\Bodega\LoteProducto', 'idbodprod', 'idbodp');
    }

    public function existencia(){
        return $this->belongsTo('App\Models\Bodega\Existencia', 'idbodprod', 'idbodprod');
    }

    public function existencias(){
        return $this->belongsTo('App\Models\Bodega\Existencia', 'idbodprod', 'idbodprod');
    }

    public function loteProd(){
        return $this->hasMany('App\Models\Bodega\LoteProducto', 'idbodprod', 'idbodp');
    }

}
?>