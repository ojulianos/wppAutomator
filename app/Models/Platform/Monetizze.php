<?php

namespace App\Models\Platform;

use App\Models\DefaultPlatform;
use App\Models\Phone;

class Monetizze extends DefaultPlatform
{
    protected array $events = [
        1 => 'AGUARDANDO_PAGAMENTO',
        2 => 'FINALIZADA_APROVADA',
        3 => 'CANCELADA',
        4 => 'DEVOLVIDA',
        5 => 'BLOQUEADA',
        6 => 'COMPLETA',
        7 => 'ABANDONO_DE_CHECKOUT',
        8 => 'OCULTO',
        98 => 'CARTAO',
        99 => 'BOLETO',
        101 => 'ASSINATURA_ATIVA',
        102 => 'ASSINATURA_INADIMPLENTE',
        103 => 'ASSINATURA_CANCELADA',
        104 => 'ASSINATURA_AGUARDANDO_PAGAMENTO'
    ];

    public function __construct(Phone $phone)
    {
        $this->checkToken($phone);
    }

    /*
     * Recebe request()->comprador
     * */
    public function setUser($user): void
    {
        $this->name             = $user['nome'];
        $this->email            = $user['email'];
        $this->birth_date       = $user['data_nascimento'];
        $this->document         = $user['cnpj_cpf'];
        $this->phone            = $user['telefone'];
        $this->cep              = $user['cep'];
        $this->endereco         = $user['endereco'];
        $this->numero           = $user['numero'];
        $this->complemento      = $user['complemento'];
        $this->bairro           = $user['bairro'];
        $this->cidade           = $user['cidade'];
        $this->estado           = $user['estado'];
    }

    /*
     * Recebe request()->venda_item_order_bump
     * */
    public function setItems($items = []): void
    {
        foreach ($items  as $item) {
            $this->items[] = [
                'produto'       => $item['produto'],
                'chave'         => $item['chave'],
                'nome'          => $item['nome'],
                'descricao'     => $item['descricao'],
                'plano'         => $item['plano'],
                'cupom'         => $item['cupom'],
                'valor'         => $item['valor'],
                'quantidade'    => $item['quantidade'],
                'principal'     => $item['principal'],
            ];
        }
    }

    /*
     * Recebe request()->venda
     * */
    public function setSale($sale): void
    {
        $this->sale = (object) [
            'id'                => $sale['codigo'],
            'plano'             => $sale['plano'],
            'cupom'             => $sale['cupom'],
            'data_compra'       => $sale['dataFinalizada'],
            'forma_pagamento'   => $sale['formaPagamento'],
            'parcelas'          => $sale['parcelas'],
            'status'            => $sale['status'],
            'valor'             => $sale['valor'],
            'frete'             => $sale['descr_tipo_frete'],
            'valor_frete'       => $sale['frete'],
            'linkBoleto'        => $sale['linkBoleto'],
            'linha_digitavel'   => $sale['linha_digitavel'],
        ];

    }

    public function checkToken(Phone $phone)
    {
        $chaveUnica = request()->chave_unica;
        if($phone->platform_api_key  != $chaveUnica) {
            exit;
        }
    }

}
