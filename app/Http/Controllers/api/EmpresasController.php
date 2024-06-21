<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\empresas;
use Illuminate\Http\Request;

class EmpresasController extends Controller
{
    public function index(Request $request){
        $d = empresas::all();
        return $d;
    }
}
