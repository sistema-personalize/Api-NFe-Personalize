<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Valid
{

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $venda = $request->venda;
        $documento = $request->documento;
        $emitente = $request->emitente;
        $destinatario = $request->destinatario;
        $itens = $request->itens;
        $frete = $request->frete;
        $respTecnico = $request->responsavel_tecnico;
        $pagamento = $request->pagamento;
        $fatura = $request->fatura;
        $duplicatas = $request->duplicatas;
        $tributacao = $request->tributacao;

        $errArr = [];
        $errArr = $this->validaDocumento($documento, $errArr);
        $errArr = $this->validaEmitente($emitente, $errArr);
        $errArr = $this->validaDestinatario($destinatario, $errArr);
        $errArr = $this->validaItens($itens, $errArr);
        if ($frete != null) $errArr = $this->validaFrete($frete, $errArr);
        $errArr = $this->validaRespTecnico($respTecnico, $errArr);
        $errArr = $this->validaPagamento($pagamento, $errArr);
        $errArr = $this->validaFaturaDuplicatas($fatura, $duplicatas, $errArr);

        if (count($errArr) == 0) return $next($request);
        else return response()->json($errArr, 401);
    }

    private function validaDocumento($documento, $errArr)
    {
        if (!isset($documento['numero_nf']) || $documento['numero_nf'] == 0) {
            array_push($errArr, "Numero de NF-e inválido");
        }
        if (!isset($documento['natureza_operacao']) || strlen($documento['natureza_operacao']) == 0) {
            array_push($errArr, "Natureza de Operação inválida");
        }
        
        if (!isset($documento['numero_serie']) || strlen($documento['numero_serie']) == 0) {
            array_push($errArr, "Número de série inválido");
        }
        if (
            !isset($documento['ambiente']) || $documento['ambiente'] != 1 &&
            $documento['ambiente'] != 2
        ) {
            array_push($errArr, "Ambiente inválido");
        }
        if (!isset($documento['info_complementar'])) {
            array_push($errArr, "Informe a info complementar ao menos vazia");
        }
        if (
            !isset($documento['consumidor_final']) || $documento['consumidor_final'] != 0 &&
            $documento['consumidor_final'] != 1
        ) {
            array_push($errArr, "Consumidor final inválido");
        }
        if (
            !isset($documento['operacao_interestadual']) || $documento['operacao_interestadual'] != 1 &&
            $documento['operacao_interestadual'] != 2
        ) {
            array_push($errArr, "Operação interestadual inválido");
        }
        if (!isset($documento['CSC']) || strlen($documento['CSC']) == 0) {
            array_push($errArr, "CSC inválido");
        }
        if (!isset($documento['CSCid']) || strlen($documento['CSCid']) == 0) {
            array_push($errArr, "CSCid inválido");
        }

        return $errArr;
    }

    private function validaEmitente($emitente, $errArr)
    {
        if (!isset($emitente['codigo_uf']) || $emitente['codigo_uf'] < 10) {
            array_push($errArr, "Código da UF emitente inválido");
        }
        if (!isset($emitente['razao_social']) || strlen($emitente['razao_social']) == 0) {
            array_push($errArr, "Razão social emitente inválida");
        }
        if (!isset($emitente['nome_fantasia']) || strlen($emitente['nome_fantasia']) == 0) {
            array_push($errArr, "Nome fantasia emitente inválida");
        }
        if (!isset($emitente['ie']) || strlen($emitente['ie']) == 0) {
            array_push($errArr, "Inscrição estadual emitente inválida");
        }
        if (!isset($emitente['cnpj']) || !$this->validar_cnpj($emitente['cnpj'])) {
            array_push($errArr, "CNPJ emitente inválido");
        }
        
        if (
            !isset($emitente['crt']) || $emitente['crt'] != 1 &&
            $emitente['crt'] != 2 && $emitente['crt'] != 3
        ) {
            array_push($errArr, "CRT inválido");
        }
        if (!isset($emitente['numero']) || strlen($emitente['numero']) == 0) {
            array_push($errArr, "Número emitente inválido");
        }
        if (!isset($emitente['logradouro']) || strlen($emitente['logradouro']) == 0) {
            array_push($errArr, "Logradouro emitente inválido");
        }
        if (!isset($emitente['complemento'])) {
            array_push($errArr, "Informe o complemento emitente ao menos vazio");
        }
        if (!isset($emitente['bairro']) || strlen($emitente['bairro']) == 0) {
            array_push($errArr, "Bairro emitente inválido");
        }
        if (!isset($emitente['nome_municipio']) || strlen($emitente['nome_municipio']) == 0) {
            array_push($errArr, "Nome município emitente inválido");
        }
        if (!isset($emitente['cod_municipio_ibge']) || strlen($emitente['cod_municipio_ibge']) != 7) {
            array_push($errArr, "Código município emitente inválido");
        }
        if (!isset($emitente['uf']) || strlen($emitente['uf']) != 2) {
            array_push($errArr, "UF emitente inválida");
        }
        if (!isset($emitente['cep']) || strlen($emitente['cep']) != 8) {
            array_push($errArr, "CEP emitente inválida");
        }
        if (!isset($emitente['nome_pais']) || strlen($emitente['nome_pais']) == 0) {
            array_push($errArr, "Nome do pais emitente inválido");
        }
        if (!isset($emitente['cod_pais']) || strlen($emitente['cod_pais']) != 4) {
            array_push($errArr, "Código do pais emitente inválido");
        }

        return $errArr;
    }

    private function validaDestinatario($destinatario, $errArr)
    {

        if (!isset($destinatario['nome']) || strlen($destinatario['nome']) == 0) {
            array_push($errArr, "Nome destinatário inválida");
        }
        if (!isset($destinatario['tipo']) || $destinatario['tipo'] != 'j' && $destinatario['tipo'] != 'f') {
            array_push($errArr, "Tipo destinatário inválida");
        }
        if (!isset($destinatario['cpf_cnpj']) || !$this->validar_cnpj($destinatario['cpf_cnpj'])) {
            array_push($errArr, "CPF/CNPJ destinatário inválido");
        }
        if (!isset($destinatario['ie_rg']) || strlen($destinatario['ie_rg']) == 0) {
            array_push($errArr, "Inscrição estadual destinatário inválida");
        }
        if (
            !isset($destinatario['contribuinte']) || $destinatario['contribuinte'] != 1 &&
            $destinatario['contribuinte'] != 0
        ) {
            array_push($errArr, "Contribuiente destinatário inválido");
        }

        if (!isset($destinatario['logradouro']) || strlen($destinatario['logradouro']) == 0) {
            array_push($errArr, "Logradouro destinatario inválido");
        }
        if (!isset($destinatario['numero']) || strlen($destinatario['numero']) == 0) {
            array_push($errArr, "Número destinatario inválido");
        }
        if (!isset($destinatario['complemento'])) {
            array_push($errArr, "Informe o complemento destinatario ao menos vazio");
        }
        if (!isset($destinatario['bairro']) || strlen($destinatario['bairro']) == 0) {
            array_push($errArr, "Bairro destinatario inválido");
        }
        if (!isset($destinatario['nome_municipio']) || strlen($destinatario['nome_municipio']) == 0) {
            array_push($errArr, "Nome município destinatario inválido");
        }
        if (!isset($destinatario['cod_municipio_ibge']) || strlen($destinatario['cod_municipio_ibge']) != 7) {
            array_push($errArr, "Código município destinatario inválido");
        }
        if (!isset($destinatario['uf']) || strlen($destinatario['uf']) != 2) {
            array_push($errArr, "UF destinatario inválida");
        }
        if (!isset($destinatario['cep']) || strlen($destinatario['cep']) != 8) {
            array_push($errArr, "CEP destinatario inválida");
        }
        if (!isset($destinatario['nome_pais']) || strlen($destinatario['nome_pais']) == 0) {
            array_push($errArr, "Nome do pais destinatario inválido");
        }
        if (!isset($destinatario['cod_pais']) || strlen($destinatario['cod_pais']) != 4) {
            array_push($errArr, "Código do pais destinatario inválido");
        }

        return $errArr;
    }


    private function validaItens($itens, $errArr)
    {
        $cont = 1;
        foreach ($itens as $i) {
            if (!isset($i['cod_barras']) || strlen($i['cod_barras']) != 13 && $i['cod_barras'] != 'SEM GTIN') {
                array_push($errArr, "Codigo de barras item $cont inválido");
            }
            if (!isset($i['codigo_produto']) || strlen($i['codigo_produto']) == 0) {
                array_push($errArr, "Codigo do produto item $cont inválido");
            }
            if (!isset($i['nome_produto']) || strlen($i['nome_produto']) == 0) {
                array_push($errArr, "Nome do produto item $cont inválido");
            }
            if (!isset($i['cfop']) || strlen($i['cfop']) != 4) {
                array_push($errArr, "CFOP  inválido");
            }
            if (!isset($i['ncm']) || strlen($i['ncm']) == 0) {
                array_push($errArr, "NCM do produto item $cont inválido");
            }
            if (!isset($i['unidade']) || strlen($i['unidade']) == 0) {
                array_push($errArr, "Unidade do produto item $cont inválido");
            }
            if (!isset($i['quantidade']) ||  $i['quantidade'] == 0) {
                array_push($errArr, "quantidade do produto item $cont inválido");
            }
            if (!isset($i['valor_unitario']) ||  $i['valor_unitario'] == 0) {
                array_push($errArr, "Valor unitário do produto item $cont inválido");
            }
            if (!isset($i['cst_csosn']) || $i['cst_csosn'] == '') {
                array_push($errArr, "Declare o CST CSOSN");
            }
            if (!isset($i['cst_pis']) || $i['cst_pis'] == '') {
                array_push($errArr, "Declare o CST PIS");
            }
            if (!isset($i['cst_cofins']) || $i['cst_cofins'] == '') {
                array_push($errArr, "Declare o CST COFINS");
            }
            if (!isset($i['cst_ipi']) || $i['cst_ipi'] == '') {
                array_push($errArr, "Declare o CST IPI");
            }

            if (!isset($i['perc_icms'])) {
                array_push($errArr, "Declare o percentual de ICMS");
            }
            if (!isset($i['perc_pis'])) {
                array_push($errArr, "Declare o percentual de PIS");
            }
            if (!isset($i['perc_cofins'])) {
                array_push($errArr, "Declare o percentual de COFINS");
            }
            if (!isset($i['perc_ipi'])) {
                array_push($errArr, "Declare o percentual de IPI");
            }
            if (
                !isset($i['compoe_valor_total']) ||  $i['compoe_valor_total'] != 0 &&
                $i['compoe_valor_total'] != 1
            ) {
                array_push($errArr, "Valor compõe o total do produto item $cont inválido");
            }

            $cont++;
        }

        return $errArr;
    }

    private function validaFrete($frete, $errArr)
    {

        if (
            !isset($frete['modelo']) || $frete['modelo'] == 1 && $frete['modelo'] == 2 && $frete['modelo'] == 3
            && $frete['modelo'] == 4 && $frete['modelo'] == 9
        ) {
            array_push($errArr, "Modelo de frete inválido");
        }
        if (!isset($frete['quantidade_volumes']) || $frete['quantidade_volumes'] == 0) {
            array_push($errArr, "Quantidade de volumes de frete inválido");
        }
        if (!isset($frete['valor']) || $frete['valor'] == 0) {
            array_push($errArr, "Valor de frete inválido");
        }
        if (!isset($frete['especie']) || strlen($frete['especie']) == 0) {
            array_push($errArr, "Espécie de frete inválida");
        }

        if (!isset($frete['peso_liquido']) || $frete['peso_liquido'] == 0) {
            array_push($errArr, "Peso liquido de frete inválido");
        }
        if (!isset($frete['peso_bruto']) || $frete['peso_bruto'] == 0) {
            array_push($errArr, "Peso bruto de frete inválido");
        }
        return $errArr;
    }

    private function validaRespTecnico($respTecnico, $errArr)
    {

        if (!isset($respTecnico['cnpj']) || !$this->validar_cnpj($respTecnico['cnpj'])) {
            array_push($errArr, "CNPJ responsável técnico inválido");
        }
        if (!isset($respTecnico['contato']) || strlen($respTecnico['contato']) == 0) {
            array_push($errArr, "Contato responsável técnico inválido");
        }
        if (!isset($respTecnico['email']) || strlen($respTecnico['email']) == 0) {
            array_push($errArr, "Email responsável técnico inválido");
        }
        if (!isset($respTecnico['telefone']) || strlen($respTecnico['telefone']) == 0) {
            array_push($errArr, "Telefone responsável técnico inválido");
        }
        return $errArr;
    }

    private function validaPagamento($pagamento, $errArr)
    {

        if (!isset($pagamento['tipo']) || strlen($pagamento['tipo']) == 0) {
            array_push($errArr, "tipo pagamento inválido");
        }
        if (
            !isset($pagamento['indicacao_pagamento']) || $pagamento['indicacao_pagamento'] != 0 &&
            $pagamento['indicacao_pagamento'] != 1 && $pagamento['indicacao_pagamento'] != 2
        ) {
            array_push($errArr, "Indicação pagamento inválido");
        }
        return $errArr;
    }

    private function validaFaturaDuplicatas($fatura, $duplicatas, $errArr)
    {

        if (!isset($fatura['desconto']) || strlen($fatura['desconto']) < 0) {
            array_push($errArr, "Desconto inválido");
        }
        if (!isset($fatura['total_nf']) || strlen($fatura['total_nf']) == 0) {
            array_push($errArr, "Total da NF-e inválido");
        }

        $cont = 1;
        $soma = 0;
        foreach ($duplicatas as $d) {
            if (!isset($d['data_vencimento']) || strlen($d['data_vencimento']) != 10) {
                array_push($errArr, "Data de vencimento duplicata $cont inválido");
            }
            if (!isset($d['valor']) || strlen($d['valor']) == 0) {
                array_push($errArr, "Valor de duplicata $cont inválido");
            }
            $cont++;
            $soma += $d['valor'];
        }

        if($soma != $fatura['total_nf']){
            array_push($errArr, "Soma das duplicatas difere do valor total da NF-e");
        }

        return $errArr;
    }

    private function validar_cnpj($cnpj)
    {
        if (strlen($cnpj) == 11) {
            return $this->valida_cpf($cnpj);
        } else {
            $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

            // Valida tamanho
            if (strlen($cnpj) != 14)
                return false;

            // Verifica se todos os digitos são iguais
            if (preg_match('/(\d)\1{13}/', $cnpj))
                return false;

            // Valida primeiro dígito verificador
            for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
                $soma += $cnpj{
                    $i} * $j;
                    $j = ($j == 2) ? 9 : $j - 1;
                }

                $resto = $soma % 11;

                if ($cnpj{
                    12} != ($resto < 2 ? 0 : 11 - $resto))
                    return false;

            // Valida segundo dígito verificador
                    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
                        $soma += $cnpj{
                            $i} * $j;
                            $j = ($j == 2) ? 9 : $j - 1;
                        }

                        $resto = $soma % 11;

                        return $cnpj{
                            13} == ($resto < 2 ? 0 : 11 - $resto);
                        }
                    }

                    function valida_cpf($cpf)
                    {

        // Extrai somente os números
                        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
                        if (strlen($cpf) != 11) {
                            return false;
                        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
                        if (preg_match('/(\d)\1{10}/', $cpf)) {
                            return false;
                        }

        // Faz o calculo para validar o CPF
                        for ($t = 9; $t < 11; $t++) {
                            for ($d = 0, $c = 0; $c < $t; $c++) {
                                $d += $cpf{
                                    $c} * (($t + 1) - $c);
                                }
                                $d = ((10 * $d) % 11) % 10;
                                if ($cpf{
                                    $c} != $d) {
                                    return false;
                                }
                            }
                            return true;
                        }
                    }
