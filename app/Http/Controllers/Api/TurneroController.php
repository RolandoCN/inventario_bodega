<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\AreaEspecialidad;

class TurneroController extends Controller
{
   
    public function index(Request $request){
        return view('turnero.index');
    }

   
}
