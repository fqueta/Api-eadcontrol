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
        if($sec=='salvarMatriculas'){
            $id_curso = $request->get('id_curso')?$request->get('id_curso'):1;
            $alunos = Matricula::where('id_curso',$id_curso)->where('excluido','n')->get()->toArray();
            if(is_array($alunos)){
                foreach ($alunos as $k => $v) {
                    unset($v['id'],
                        $v['data'],
                        $v['atualizado'],
                        $v['data_conclusao'],
                        $v['data_certificado'],
                        $v['data_cancela_agenda'],
                        $v['data_solicit_certificado'],
                        $v['data_blacklist'],
                        $v['data_seguir'],
                        $v['data_documento'],
                    );
                    $v['token'] = uniqid();
                    $v['id_curso'] = 44;
                    $v['id_turma'] = 'a';
                    $v['status'] = 2;
                    $v['status'] = 2;
                    $v['memo'] = 'Via Api';
                    $v['validade'] = 365;
                    // $v['data'] = date('Y-m-d H:i:s');
                    $v['data_situacao'] = date('Y-m-d H:i:s');
                    $v['data_matricula'] = date('Y-m-d H:i:s');
                    $v['data_agendamento'] = date('Y-m-d H:i:s');
                    $v['data_inicio'] = date('Y-m-d H:i:s');
                    $v['data_contrato'] = date('Y-m-d H:i:s');
                    $v['tag_sys'] = Qlib::lib_array_json(['add_sisema']);
                    $v['historico'] = Qlib::lib_array_json([
                        'data'=>date('d/m/Y H:i:s'),
                        'autor'=>'sistema',
                        'evento'=>'criado',
                        'status_estenso'=>'Matriculado',
                    ]);
                    $ret['salv'][$k] = (new MatriculasController)->salvarMatriculas($v); //
                    if(isset($ret['salv'][$k]['exec']) && $ret['salv'][$k]['exec']){
                        $ret['exec'] = $ret['salv'][$k]['exec'];
                        $ret['mens'] = @$ret['salv'][$k]['mens'];
                    }else{
                        $ret['mens'] = @$ret['salv'][$k]['mens'];
                    }
                }
            }
        }
        return response()->json($ret) ;
    }
}
