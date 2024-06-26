<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculasController extends Controller
{
    public $table;
    public function __construct()
    {
        $this->table = 'matriculas';
    }
    public function index(Request $request)
    {
        // dd($request->get('status'));
        // $sess = session()->get('tenancy'); informações do tenancy

        $d = DB::table($this->table)->select('matriculas.*','clientes.Nome','clientes.sobrenome','clientes.Email')
        ->join('clientes', 'clientes.id','=','matriculas.id_cliente')
        ->where('matriculas.excluido','=','n')->where('matriculas.deletado','=','n')->orderBy('matriculas.id','asc');
        $limit = 25;
        if($request->has('limit')){
            $limit = $request->get('limit');
        }
        if($request->has('status')){
            if($request->get('status')=='todos_matriculados'){
                $d = $d->where('matriculas.status', '!=',1);
            }else{
                $d = $d->where('matriculas.status', '=',$request->get('status'));
            }
        }
        if($request->has('token_externo')){
            $tkex = $request->get('token_externo');
            if($tkex=='null'){
                $d = $d->whereNull('matriculas.token_externo');
            }elseif(is_null($tkex)){
                $d = $d->whereNotNull('matriculas.token_externo');
            }else{
                $d = $d->where('matriculas.token_externo', '=',$request->get('token_externo'));
            }
        }
        if($request->has('id_cliente')){
            $d = $d->where('matriculas.id_cliente', '=',$request->get('id_cliente'));
        }
        if($limit=='todos'){
            $d = $d->get();
        }else{
            $d = $d->paginate($limit);
        }
        $exibe_contrato = $request->has('contrato') ? $request->has('contrato') : 's';
        $ret['exec'] = false;
        $ret['status'] = 404;
        $ret['total'] = 0;
        $ret['data'] = [];
        if($d->count() > 0){
            if($exibe_contrato=='s'){
                foreach ($d as $k => $v) {
                    if($nc=$this->numero_contrato($v->id)){
                        $d[$k]->numero_contrato = $nc;
                    }
                }
            }
            $ret['total'] = $d->count();
            $ret['data'] = $d;
            $ret['exec'] = true;
            $ret['status'] = 200;
        }
        return $ret;
    }
    /**
     * Metodo para exibir o numero do contrato
     * @param int $id_matricula
     */
    public function numero_contrato($id_matricula=false){
        $ret = false;
        if($id_matricula){
            //uso $ret = cursos::numero_contrato($id_matricula);
            $ret = false;
            if($id_matricula){
                $json_contrato = Qlib::buscaValorDb0('matriculas','id',$id_matricula,'contrato');
                $arr_contrato = Qlib::lib_json_array($json_contrato);
                if(isset($arr_contrato['data_aceito_contrato']) && !empty($arr_contrato['data_aceito_contrato'])){
                    $arrd = explode('-',$arr_contrato['data_aceito_contrato']);
                    if(isset($arrd[1])){
                        $ret = $id_matricula.'.'.$arrd[1].'.'.$arrd[0];
                    }
                }

            }
            return $ret;
        }
    }
    /**
     * Metodos para salvar um orçamento assinado para ser exibido dps no painel do CRM.
     * @param string $token_matricula,array $dm= dados da matricula
     */
    public function salva_orcamento_assinado($token=false,$dm=false){
        $ret['exec'] = false;
        $campo_meta1 = 'assinado';
        $campo_meta2 = 'contrato_assinado';
        // $campo_meta3 = 'total_assinado';
        if($token && !$dm){
            $dm = Matricula::where('token',$token)->get();
            if($dm->count() > 0){
                $dm = $dm[0];
            }
        }
        $ret['dm'] = $dm;
        if(isset($dm['id']) && isset($dm['contrato']) && isset($dm['orc'])  && isset($dm['total'])){
            $ret['s1'] = Qlib::update_matriculameta($dm['id'],$campo_meta1,'s');
            $ret['s2'] = Qlib::update_matriculameta($dm['id'],$campo_meta2,Qlib::lib_array_json([
                'orc'=>$dm['orc'],
                'totais'=>@$dm['totais'],
                'subtotal'=>@$dm['subtotal'],
                'total'=>@$dm['total'],
                'cliente_id'=>@$dm['id_cliente'],
                'porcentagem_comissao'=>@$dm['porcentagem_comissao'],
                'comissao'=>@$dm['valor_comissao'],
            ]));
            if($ret['s1'] && $ret['s2']){
                $ret['exec'] = true;
            }
        }
        // $ret['dm'] = $dm;
        return $ret;
    }
    /**
     * Metodo para retornar um array com os dados do contrato assinado
     */
    public function get_matricula_assinado($token=false){
        $matricula_id = Qlib::get_matricula_id_by_token($token);
        //verifica se está assinado
        $ret['exec'] = false;
        $ret['data'] = [];
        if(!$matricula_id){
            return $ret;
        };
        $campo_meta1 = 'assinado';
        $campo_meta2 = 'contrato_assinado';
        $ver = Qlib::get_matriculameta($matricula_id,$campo_meta1,true);
        if($ver=='s'){
            $data = Qlib::get_matriculameta($matricula_id,$campo_meta2,true);
            if($data){
                $ret['exec'] = true;
                $dm = Matricula::select('matriculas.*','clientes.Nome','clientes.sobrenome')
                ->join('clientes','matriculas.id_cliente','=','clientes.id')
                ->where('matriculas.token',$token)
                ->get();
                if($dm->count() > 0){
                    $dm = $dm->toArray();
                    $dm = $dm[0];
                }
                $ret['dm'] = $dm;
                $ret['data'] = Qlib::lib_json_array($data);
                $aer = DB::table('aeronaves')->where('excluido', '=','n')->where('deletado', '=','n')->get();
                $aeronaves_arr = [];
                if(count($aer)!=0){
                    foreach ($aer as $ka => $va) {
                        $aeronaves_arr[$va->id] = $va->nome;
                    }
                }
                $ret['aeronaves'] = $aeronaves_arr;
            }
        }
        return $ret;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    /**
     * Metodo para adicionar um curso para todo aluno que está no curso de mentoria
     */
    public function add_aluno(Request $request){
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
                        // $v['data_inicio'],
                        $v['data_documento'],
                        // $v['data_contrato'],
                        $v['data_matricula'],
                        $v['contrato'],
                    );
                    $v['token'] = uniqid();
                    $v['id_curso'] = 44;
                    $v['id_turma'] = 'a';
                    $v['status'] = 2;
                    $v['status'] = 2;
                    $v['memo'] = 'Via Api';
                    $v['ativo'] = 's';
                    $v['validade'] = 365;
                    // $v['data'] = date('Y-m-d H:i:s');
                    $v['data_situacao'] = date('Y-m-d H:i:s');
                    // $v['data_matricula'] = date('Y-m-d H:i:s');
                    $v['data_agendamento'] = date('Y-m-d');
                    $v['data_inicio'] = date('Y-m-d H:i:s');
                    $v['data_contrato'] = date('Y-m-d H:i:s');
                    $v['tag_sys'] = Qlib::lib_array_json(['add_sisema']);
                    $v['historico'] = Qlib::lib_array_json([
                        'data'=>date('d/m/Y H:i:s'),
                        'autor'=>'sistema',
                        'evento'=>'criado',
                        'status_estenso'=>'Matriculado',
                    ]);
                    $ret['salv'][$k] = $this->salvarMatriculas($v); //
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
    /**
     * metodo para salvar uma matrícula
     * @param $d = dados para serem salvos no banco de dados
     */
    public function salvarMatriculas($d=[]){
        $ret['exec'] = false;
        $ret['dados'] = $d;
        if(isset($d['id_cliente']) && !empty($d['id_cliente']) && isset($d['id_curso']) && !empty($d['id_curso'])){
            $enc = Matricula::where('id_cliente', $d['id_cliente'])->where('id_curso', $d['id_curso'])->where('excluido', 'n')->get();
            if($enc->count() > 0){
                //update
                try {
                    $save = Matricula::where('id_cliente', $d['id_cliente'])->where('id_curso', $d['id_curso'])->update($d);
                    $ret['exec'] = true;
                    $ret['mens'] = 'Atualizado com sucesso! id_cliente='.$d['id_cliente'].' id_curso='.$d['id_curso'].'' ;
                    $ret['enc'] = $enc;
                } catch (\Exception $e) {
                    //throw $e;
                    $ret['exec'] = false;
                    $ret['mens'] = 'Erro ao atualizar o registro '. $e->getMessage();
                }
            }else{
                //add
                try {
                    $save = Matricula::create($d);
                    $ret['exec'] = true;
                    $ret['mens'] = 'Salvo com sucesso!';
                    $ret['salv'] = $d;
                } catch (\Exception $e) {
                    //throw $e;
                    $ret['exec'] = false;
                    $ret['mens'] = 'Erro ao salvar o registro '. $e->getMessage();
                }

            }
        }
        return $ret;
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $d = DB::table($this->table)->find($id);

        if(is_null($d)){
            $ret['exec'] = false;
            $ret['status'] = 404;
            $ret['data'] = [];
            return response()->json($ret);
        }else{
            $ret['exec'] = true;
            $ret['status'] = 200;
            $ret['data'] = $d;
            return response()->json($ret);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $d = $request->all();
        $ret['exec'] = false;
        $ret['status'] = 500;
        $ret['message'] = 'Error updating';
        if($d){
            $ret['exec'] = DB::table($this->table)->where('id',$id)->update($d);
            if($ret['exec']){
                $ret['status'] = 200;
                $ret['message'] = 'updated successfully';
                $ret['data'] = DB::table($this->table)->find($id);
            }
        }
        return response()->json($ret);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
