<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NFCeService;
use App\Models\Certificado;
use App\Models\NFCeDocumento;
use App\Models\NFCeEmitente;
use App\Models\NFCeDestinatario;
use App\Models\NFCeItem;
use App\Models\NFCeResponsavelTecnico;
use App\Models\NFCePagamento;
use Exception;
use NFePHP\DA\NFe\Danfce;

class NFCeController extends Controller
{
    private $modelCertificado;
    private $modelDocumento;
    private $modelEmitente;
    private $modelDestinatario;
    private $modelItem;
    private $modelRespTecnico;
    private $modelPagamento;

    public function __construct(
        Certificado $certificado,
        NFCeDocumento $documento,
        NFCeEmitente $emitente,
        NFCeDestinatario $destinatario,
        NFCeItem $item,
        NFCeResponsavelTecnico $resp,
        NFCePagamento $pagamento
    ) {
        $this->modelCertificado = $certificado;
        $this->modelDocumento = $documento;
        $this->modelEmitente = $emitente;
        $this->modelDestinatario = $destinatario;
        $this->modelItem = $item;
        $this->modelRespTecnico = $resp;
        $this->modelPagamento = $pagamento;
    }

    public function all()
    {
        $docs = $this->modelDocumento->all();
        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $doc->emitente;
                $doc->destinatario;
                $doc->itens;
                $doc->respTecnico;
                $doc->pagamento;
            }

            return response()->json($docs, 200);
        } else {
            return response()->json("Nenhum documento encontrado", 404);
        }
    }

    public function first($id)
    { // nfe por id
        $doc = $this->modelDocumento->find($id);
        if ($doc != null) {
            $doc->emitente;
            $doc->destinatario;
            $doc->itens;
            $doc->respTecnico;
            $doc->pagamento;

            return response()->json($doc, 200);
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function xml($id)
    { // nfe por id
        $doc = $this->modelDocumento->find($id);

        if (file_exists("public/xml_nfce/$doc->chave.xml")) {

            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename='.$doc->numero_nf.'.xml');
            header('Pragma: no-cache');
            readfile("public/xml_nfce/$doc->chave.xml");

        } else {
            return response()->json('XML não enontrado!', 401);
        }
    }

    public function gerarXml(Request $request)
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

        $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

        $nfce_service = new NFCeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $documento['CSC'],
            "CSCid" => $documento['CSCid']
        ], $certificado);

        $n = $nfce_service->gerar(
            $documento,
            $emitente,
            $destinatario,
            $itens,
            $frete,
            $respTecnico,
            $pagamento,
            $fatura,
            $duplicatas
        );

        return  response()->json($n['xml'], 201);
    }

    public function transmitir(Request $request)
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

        $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

        $nfce_service = new NFCeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $documento['CSC'],
            "CSCid" => $documento['CSCid']
        ], $certificado);

        $n = $nfce_service->gerar(
            $documento,
            $emitente,
            $destinatario,
            $itens,
            $frete,
            $respTecnico,
            $pagamento,
            $fatura,
            $duplicatas
        );
        $signed = $nfce_service->sign($n['xml']);
        $resultado = $nfce_service->transmitir($signed, $n['chave']);

        if (isset($resultado['protNFe']['infProt']['xMotivo'])) {
            return response()->json($resultado['protNFe']['infProt']['xMotivo'], 401);
        } else if ($resultado == 'ERRO DE COMUNICAÇÃO COM WEBSERVICE SEFAZ') {
            return response()->json($resultado, 401);
        } else {

            $documento = $this->criaDocumento($venda, $documento, $n['chave']);
            $emitente = $this->salvaEmitente($emitente, $documento->id);
            $destinatario = $destinatario != null ? $this->salvaDestinatario($destinatario, $documento->id) : null;
            $itens = $this->salvarItens($itens, $documento->id);
            $respTecnico = $this->salvaRespTecnico($respTecnico, $documento->id);
            $pagamento = $this->salvaPagamento($pagamento, $documento->id);

            $doc = $this->modelDocumento->find($documento->id);

            $retorno = [
                'chave' => $n['chave'],
                'recibo' => $resultado,
                'documento_id' => $doc->id
            ];
            return response()->json($retorno, 201);
        }
    }


    private function criaDocumento($venda, $documento, $chave, $estado = 'APROVADO')
    {
        $arr = [
            'comentario' => $venda['comentario'],
            'identificacao' => $venda['identificacao'],
            'numero_nfce' => $documento['numero_nfce'],
            'natureza_operacao' => $documento['natureza_operacao'],
            'numero_serie' => $documento['numero_serie'],
            'ambiente' => $documento['ambiente'],
            'info_complementar' => $documento['info_complementar'],
            'consumidor_final' => $documento['consumidor_final'],
            'operacao_interestadual' => $documento['operacao_interestadual'],
            'CSC' => $documento['CSC'],
            'CSCid' => $documento['CSCid'],
            'chave' => $chave,
            'estado' => $estado
        ];

        $res = $this->modelDocumento->create($arr);
        return $res;
    }

    private function salvaEmitente($emitente, $documentoId)
    {
        $arr = [
            'codigo_uf' => $emitente['codigo_uf'],
            'razao_social' => $emitente['razao_social'],
            'nome_fantasia' => $emitente['nome_fantasia'],
            'ie' => $emitente['ie'],
            'cnpj' => $emitente['cnpj'],
            'crt' => $emitente['crt'],
            'logradouro' => $emitente['logradouro'],
            'numero' => $emitente['numero'],
            'complemento' => $emitente['complemento'],
            'bairro' => $emitente['bairro'],
            'nome_municipio' => $emitente['nome_municipio'],
            'cod_municipio_ibge' => $emitente['cod_municipio_ibge'],
            'uf' => $emitente['uf'],
            'cep' => $emitente['cep'],
            'nome_pais' => $emitente['nome_pais'],
            'cod_pais' => $emitente['cod_pais'],
            'documento_id' => $documentoId,
        ];
        $this->modelEmitente->create($arr);
    }

    private function salvaDestinatario($destinatario, $documentoId)
    {
        $arr = [
            'nome' => $destinatario['nome'],
            'tipo' => $destinatario['tipo'],
            'cpf_cnpj' => $destinatario['cpf_cnpj'],
            'ie_rg' => $destinatario['ie_rg'],
            'contribuinte' => $destinatario['contribuinte'],
            'logradouro' => $destinatario['logradouro'],
            'numero' => $destinatario['numero'],
            'complemento' => $destinatario['complemento'],
            'bairro' => $destinatario['bairro'],
            'nome_municipio' => $destinatario['nome_municipio'],
            'cod_municipio_ibge' => $destinatario['cod_municipio_ibge'],
            'uf' => $destinatario['uf'],
            'cep' => $destinatario['cep'],
            'nome_pais' => $destinatario['nome_pais'],
            'cod_pais' => $destinatario['cod_pais'],
            'documento_id' => $documentoId
        ];
        $this->modelDestinatario->create($arr);
    }

    private function salvarItens($itens, $documentoId)
    {
        foreach ($itens as $i) {
            $arr = [
                'cod_barras' => $i['cod_barras'],
                'codigo_produto' => $i['codigo_produto'],
                'nome_produto' => $i['nome_produto'],
                'ncm' => $i['ncm'],
                'cfop' => $i['cfop'],
                'unidade' => $i['unidade'],
                'quantidade' => $i['quantidade'],
                'valor_unitario' => $i['valor_unitario'],
                'compoe_valor_total' => $i['compoe_valor_total'],
                'cst_csosn' => $i['cst_csosn'],
                'cst_pis' => $i['cst_pis'],
                'cst_cofins' => $i['cst_cofins'],
                'cst_ipi' => $i['cst_ipi'],
                'perc_icms' => $i['perc_icms'],
                'perc_pis' => $i['perc_pis'],
                'perc_cofins' => $i['perc_cofins'],
                'perc_ipi' => $i['perc_ipi'],

                'documento_id' => $documentoId
            ];
            $this->modelItem->create($arr);
        }
    }

    private function salvaRespTecnico($resp, $documentoId)
    {
        $arr = [
            'cnpj' => $resp['cnpj'],
            'contato' => $resp['contato'],
            'email' => $resp['email'],
            'telefone' => $resp['telefone'],
            'documento_id' => $documentoId,
        ];
        $this->modelRespTecnico->create($arr);
    }

    private function salvaPagamento($pagamento, $documentoId)
    {
        $arr = [
            'tipo' => $pagamento['tipo'],
            'indicacao_pagamento' => $pagamento['indicacao_pagamento'],
            'documento_id' => $documentoId,
        ];
        $this->modelPagamento->create($arr);
    }

    public function cancelarPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $justificativa = $request->justificativa;

        if (strlen($justificativa) < 15) {
            return response()->json("Informe a justificativa com no minimo 15 caracteres", 404);
            die();
        }

        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $documento->CSC,
                "CSCid" => $documento->CSCid
            ], $certificado);

            $res = $nfe_service->cancelar($documento, $justificativa);
            if ($res['retEvento']['infEvento']['xMotivo'] == 'Evento registrado e vinculado a NF-e') {
                $documento->estado = 'CANCELADO';
                $documento->save();
            }
            return response()->json($res['retEvento']['infEvento'], 200);
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function cancelarPorChave(Request $request)
    {
        $chave = $request->chave;
        $justificativa = $request->justificativa;

        if (strlen($justificativa) < 15) {
            return response()->json("Informe a justificativa com no minimo 15 caracteres", 404);
            die();
        }

        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $documento->CSC,
                "CSCid" => $documento->CSCid
            ], $certificado);

            $res = $nfe_service->cancelar($documento, $justificativa);
            if ($res['retEvento']['infEvento']['xMotivo'] == 'Evento registrado e vinculado a NF-e') {
                $documento->estado = 'CANCELADO';
                $documento->save();
            }
            return response()->json($res['retEvento']['infEvento'], 200);
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function consultarPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $documento->CSC,
                "CSCid" => $documento->CSCid
            ], $certificado);

            $res = $nfe_service->consultar($documento);
            return response()->json($res['protNFe']['infProt'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultarPorChave(Request $request)
    {
        $chave = $request->chave;
        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $documento->CSC,
                "CSCid" => $documento->CSCid
            ], $certificado);

            $res = $nfe_service->consultar($documento);
            return response()->json($res['protNFe']['infProt'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function imprimirPorDocumento($id)
    {
        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $xml = file_get_contents('public/xml_nfce/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/logo.jpg'));

            try {
                $danfe = new Danfce($xml);
                $id = $danfe->monta($logo);
                $pdf = $danfe->render();
                header('Content-Type: application/pdf');
                echo $pdf;
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirPorChave($chave)
    {
        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $xml = file_get_contents('public/xml_nfce/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/logo.jpg'));

            try {
                $danfe = new Danfce($xml);
                $id = $danfe->monta($logo);
                $pdf = $danfe->render();
                header('Content-Type: application/pdf');
                echo $pdf;
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

     public function inutilizar(Request $request){
        try{

            $certificado = $this->modelCertificado
            ->where('cnpj', $request->cnpj)->first();

            $emitente = $this->modelEmitente
            ->where('cnpj', $request->cnpj)->first();

            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $request->ambiente,
                "razaosocial" => $request->razao_social,
                "siglaUF" => $request->uf,
                "cnpj" => $request->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $certificado);

            $result = $nfe_service->inutilizar($request->numero_inicio, $request->numero_fim, 
                $request->justificativa);
            if($result['infInut']['cStat'] == 102){
                return response()->json($result['infInut']['xMotivo'], 200);
            }else{
                return response()->json($result['infInut']['xMotivo'], 401);
            }

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);

        }
    }
}
