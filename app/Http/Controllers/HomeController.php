<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(auth()->user()->estado=="I"){
           
            auth()->logout(); // deslogueamos 
            return redirect('/login')->with(['user_baja'=>true]); // redireccionamos al login y decimos que: "El usuario ha sido dado de baja"
        } 
       $a=\DB::table('inventario.persona')->get();
        
        return view('home');
    }
}
