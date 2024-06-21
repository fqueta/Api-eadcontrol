<?php

namespace App\Http\Middleware;

use App\Models\empresas;
use App\Qlib\Qlib;
use Closure;
use Illuminate\Http\Request;

class TenancyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $url = config('app.url');
        $empresa = $request->tenancy;
        $domain = url('');
        $subdomain = Qlib::is_subdominio();
        if($subdomain=='gerente'){
            // Qlib::selectDefaultConnection('mysql');
        }else{
            //Encontra o tenance no bando de dados que gerenciamento
            // $urlEmpresa = $empresa.'.'.$url;
            $urlEmpresa = $subdomain;
            $tenancy = empresas::where('usuario',$urlEmpresa)->firstOrFail();
            $arr_t = $tenancy->toArray();
            session()->push('tenancy', $arr_t);
            // if(isset($arr_t['sistemas']) && Qlib::isJson($arr_t['sistemas'])){
            //     $arr_sistemas = Qlib::lib_json_array($arr_t['sistemas']);
            //     $suf_in = '_cs_aero';
            //     $db = isset($arr_sistemas[$suf_in]['db_name'])?$arr_sistemas[$suf_in]['db_name']:false;
            // }
            //carrega a nova coneaxao
            Qlib::selectDefaultConnection('tenant');
            // (new Connect($tenancy))->setDefault();
        }
        return $next($request);
    }
}
