<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;
    protected $casts = [
        'historico' => 'array',
        'reg_inscricao' => 'array',
        'reg_agendamento' => 'array',
    ];
    protected $fillable = [
        'id',
        'id_cliente',
        'id_curso',
        'id_resposavel',
        'id_turma',
        'data',
        'aluno',
        'Descricao',
        'token',
        'status',
        'etapa_atual',
        'valor',
        'situacao',
        'responsavel',
        'agendamento',
        'data_agendamento',
        'data_matricula',
        'data_contrato',
        'contrato',
        'data_conclusao',
        'data_cancela_agenda',
        'data_certificado',
        'data_solicit_certificado',
        'data_cancela_agenda',
        'data_solicit_certificado',
        'data_inicio',
        'data_blacklist',
        'data_documento',
        'hora_agendamento',
        'confirmar_agenda',
        'numero',
        'obs',
        'obs_chamada',
        'validade',
        'parcelamento',
        'autor',
        'atualizado',
        'cobranca_gerada',
        'seguido_por',
        'data_seguir',
        'setor',
        'tag',
        'tag_sys',
        'ativo',
        'notific',
        'historico',
        'pagamento_asaas',
        'excluido',
        'reg_excluido',
        'total',
        'TipoDesconto',
        'reg_pagamento',
        'reg_agendamento',
        'memo',
        'data_situacao',
        'token_externo',
        'token_externo',
    ];
    const CREATED_AT = 'data';
    const UPDATED_AT = 'atualizado';
}
