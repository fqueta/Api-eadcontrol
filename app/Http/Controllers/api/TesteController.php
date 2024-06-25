<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MatriculasController;
use App\Models\Matricula;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function index(Request $request){
        $sec = $request->get('sec') ? $request->get('sec') : false;
        $ret['exec'] = false;
        $ret = (new MatriculasController)->add_aluno($request);
        return response()->json($ret) ;
    }
}
