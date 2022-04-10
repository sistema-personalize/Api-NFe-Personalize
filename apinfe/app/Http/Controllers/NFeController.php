<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NFeService;
use App\Models\Certificado;
use App\Models\Documento;
use App\Models\Emitente;
use App\Models\Destinatario;
use App\Models\Item;
use App\Models\Frete;
use App\Models\ResponsavelTecnico;
use App\Models\Pagamento;
use App\Models\Fatura;
use NFePHP\DA\NFe\Daevento;
use App\Models\Duplicata;
use Exception;
use NFePHP\DA\NFe\Danfe;
use NFePHP\Common\Certificate;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class NFeController extends Controller
{
    private $modelCertificado;
    private $modelDocumento;
    private $modelEmitente;
    private $modelDestinatario;
    private $modelItem;
    private $modelFrete;
    private $modelRespTecnico;
    private $modelPagamento;
    private $modelFatura;
    private $modelDuplicata;

    public function __construct(
        Certificado $certificado,
        Documento $documento,
        Emitente $emitente,
        Destinatario $destinatario,
        Item $item,
        Frete $frete,
        ResponsavelTecnico $resp,
        Pagamento $pagamento,
        Fatura $fatura,
        Duplicata $duplicata
    ) {
        $this->modelCertificado = $certificado;
        $this->modelDocumento = $documento;
        $this->modelEmitente = $emitente;
        $this->modelDestinatario = $destinatario;
        $this->modelItem = $item;
        $this->modelFrete = $frete;
        $this->modelRespTecnico = $resp;
        $this->modelPagamento = $pagamento;
        $this->modelFatura = $fatura;
        $this->modelDuplicata = $duplicata;
    }

    public function certificado($cnpj){
        $certificado = $this->modelCertificado->where('cnpj', $cnpj)->first();

        if($certificado == null){
            return response()->json("Nenhum certificado encontrado", 404);
        }
        
        $infoCertificado = Certificate::readPfx($certificado->arquivo, $certificado->senha);
        $publicKey = $infoCertificado->publicKey;

        $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
        $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');

        $data = [
            'serial' => $publicKey->serialNumber,
            'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
            'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
            'id' => $publicKey->commonName
        ];

        return response()->json($data, 200);
    }

    public function all()
    {
        $docs = $this->modelDocumento->all();
        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $doc->emitente;
                $doc->destinatario;
                $doc->itens;
                $doc->frete;
                $doc->respTecnico;
                $doc->pagamento;
                $doc->fatura;
                $doc->duplicatas;
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
            $doc->frete;
            $doc->respTecnico;
            $doc->pagamento;
            $doc->fatura;
            $doc->duplicatas;

            return response()->json($doc, 200);
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function xml($id)
    { // nfe por id
        $doc = $this->modelDocumento->find($id);
        if (file_exists("public/xml_nfe/$doc->chave.xml")) {

            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename=' . $doc->numero_nf . '.xml');
            header('Pragma: no-cache');
            readfile("public/xml_nfe/$doc->chave.xml");
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

        if($certificado == null){
            return response()->json("Certificado não encontrado para este cnpj de emitente", 401);
        }
        
        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ], $certificado);

        $n = $nfe_service->gerar(
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

        if(!isset($n['erros_xml'])){
            return response()->json($n['xml'], 201);
        }else{
            return response()->json($n['erros_xml'], 401);
        }
    }

    public function danfeTemporaria(Request $request)
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

        if($certificado == null){
            return response()->json("Certificado não encontrado para este cnpj de emitente", 401);
        }
        
        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ], $certificado);

        $n = $nfe_service->gerar(
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

        if(!isset($n['erros_xml'])){
            $xml = $n['xml'];
            $chave = $n['chave'];
            $danfe = new Danfe($xml);
            $id = $danfe->monta($logo);
            $pdf = $danfe->render();

            file_put_contents('public/temp/' . $chave . '.pdf', $pdf);
            
            return response()->json('public/temp/' . $chave . '.pdf', 201);
        }else{
            return response()->json($n['erros_xml'], 401);
        }
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
        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ], $certificado);

        $n = $nfe_service->gerar(
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
        if(!isset($n['erros_xml'])){
            $signed = $nfe_service->sign($n['xml']);

            $resultado = $nfe_service->transmitir($signed, $n['chave']);

            if (isset($resultado['protNFe']['infProt']['xMotivo'])) {
                return response()->json($resultado['protNFe']['infProt']['xMotivo'], 401);
            } else if ($resultado == 'ERRO DE COMUNICAÇÃO COM WEBSERVICE SEFAZ') {
                return response()->json($resultado, 401);
            } else {
                try{
                    $documento = $this->criaDocumento($venda, $documento, $n['chave']);
                    $emitente = $this->salvaEmitente($emitente, $documento->id);
                    $destinatario = $this->salvaDestinatario($destinatario, $documento->id);
                    $itens = $this->salvarItens($itens, $documento->id);
                    $frete = $frete != null ? $this->salvaFrete($frete, $documento->id) : null;
                    $respTecnico = $this->salvaRespTecnico($respTecnico, $documento->id);
                    $pagamento = $this->salvaPagamento($pagamento, $documento->id);
                    $fatura = $this->salvaFatura($fatura, $documento->id);
                    $duplicatas = $this->salvaDuplicatas($duplicatas, $documento->id);

                    $doc = $this->modelDocumento->find($documento->id);

                    $retorno = [
                        'chave' => $n['chave'],
                        'recibo' => $resultado,
                        'documento_id' => $doc->id
                    ];
                    return response()->json($retorno, 201);
                }catch(\Exception $e){
                    return response()->json("Documento gerado com erro ao armazenar no banco: " . $e->getMessage(), 401);
                }
            }
        }else{
            return response()->json($n['erros_xml'], 401);
        }
    }


    private function criaDocumento($venda, $documento, $chave, $estado = 'APROVADO')
    {
        $arr = [
            'comentario' => $venda['comentario'],
            'identificacao' => $venda['identificacao'],
            'numero_nf' => $documento['numero_nf'],
            'natureza_operacao' => $documento['natureza_operacao'],
            'numero_serie' => $documento['numero_serie'],
            'ambiente' => $documento['ambiente'],
            'info_complementar' => $documento['info_complementar'],
            'consumidor_final' => $documento['consumidor_final'],
            'operacao_interestadual' => $documento['operacao_interestadual'],
            'chave' => $chave,
            'estado' => $estado,
            'sequencia_correcao' => 0,
            'aut_xml' => $documento['aut_xml']
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

                'vBCSTRet' => isset($i['vBCSTRet']) ? $i['vBCSTRet'] : 0,
                'pST' => isset($i['pST']) ? $i['pST'] : 0,
                'vICMSSTRet' => isset($i['vICMSSTRet']) ? $i['vICMSSTRet'] : 0,
                'vICMSSubstituto' => isset($i['vICMSSubstituto']) ? $i['vICMSSubstituto'] : 0,


                'documento_id' => $documentoId
            ];
            $this->modelItem->create($arr);
        }
    }

    private function salvaFrete($frete, $documentoId)
    {
        $arr = [
            'modelo' => $frete['modelo'],
            'valor' => $frete['valor'],
            'quantidade_volumes' => $frete['quantidade_volumes'],
            'especie' => $frete['especie'],
            'placa' => $frete['placa'],
            'uf_placa' => $frete['uf_placa'],
            'peso_liquido' => $frete['peso_liquido'],
            'peso_bruto' => $frete['peso_bruto'],
            'documento_id' => $documentoId,
            'numero_volumes' => $frete['numero_volumes']
        ];
        $this->modelFrete->create($arr);
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


    private function salvaFatura($fatura, $documentoId)
    {
        $arr = [
            'desconto' => $fatura['desconto'],
            'total_nf' => $fatura['total_nf'],
            'documento_id' => $documentoId,
        ];
        $this->modelFatura->create($arr);
    }

    private function salvaDuplicatas($duplicatas, $documentoId)
    {
        foreach ($duplicatas as $dp) {
            $arr = [
                'data_vencimento' => $dp['data_vencimento'],
                'valor' => $dp['valor'],
                'documento_id' => $documentoId
            ];
            $this->modelDuplicata->create($arr);
        }
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
            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
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
            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
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

            $certificado = $this->modelCertificado
            ->where('cnpj', $documento->emitente->cnpj)->first();

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
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
            $certificado = $this->modelCertificado
            ->where('cnpj', $documento->emitente->cnpj)->first();

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
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
            $xml = file_get_contents('public/xml_nfe/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $danfe = new Danfe($xml);
                $id = $danfe->monta($logo);
                $pdf = $danfe->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
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
            $xml = file_get_contents('public/xml_nfe/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $danfe = new Danfe($xml);
                $id = $danfe->monta($logo);
                $pdf = $danfe->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCancelaPorDocumento($id)
    {
        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));


            $xml = file_get_contents('public/xml_nfe_cancelada/' . $documento->chave . '.xml');
            $dadosEmitente = $this->getEmitente($documento->emitente);
            try {

                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCancelaPorChave($chave)
    {
        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));


            $xml = file_get_contents('public/xml_nfe_cancelada/' . $documento->chave . '.xml');
            $dadosEmitente = $this->getEmitente($documento->emitente);
            try {

                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCorrecaoPorDocumento($id)
    {
        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            $xml = file_get_contents('public/xml_nfe_correcao/' . $documento->chave . '.xml');
            $dadosEmitente = $this->getEmitente($documento->emitente);
            try {

                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $daevento->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $pdf = $daevento->render($logo);
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCorrecaoPorChave($chave)
    {
        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            $xml = file_get_contents('public/xml_nfe_correcao/' . $documento->chave . '.xml');
            $dadosEmitente = $this->getEmitente($documento->emitente);
            try {

                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $daevento->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $pdf = $daevento->render($logo);
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    private function getEmitente($emitente)
    {
        return [
            'razao' => $emitente->razao_social,
            'logradouro' => $emitente->logradouro,
            'numero' => $emitente->numero,
            'complemento' => '',
            'bairro' => $emitente->bairro,
            'CEP' => $emitente->cep,
            'municipio' => $emitente->nome_municipio,
            'UF' => $emitente->uf,
            'telefone' => '',
            'email' => ''
        ];
    }


    public function correcaoPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $correcao = $request->correcao;

        if (strlen($correcao) < 15) {
            return response()->json("Informe a correção com no minimo 15 caracteres", 404);
            die();
        }

        $documento = $this->modelDocumento->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado
            ->where('cnpj', $documento->emitente->cnpj)->first();
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $certificado);

            $res = $nfe_service->cartaCorrecao($documento, $correcao);
            if ($res['retEvento']['infEvento']['xMotivo'] == 'Evento registrado e vinculado a NF-e') {
                $documento->sequencia_correcao = $documento->sequencia_correcao + 1;
                $documento->save();
                return response()->json($res['retEvento'], 200);
            }else{
                return response()->json($res['retEvento']['infEvento'], 401);
            }

        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }


    public function inutilizar(Request $request){
        try{

            $certificado = $this->modelCertificado
            ->where('cnpj', $request->cnpj)->first();

            $nfe_service = new NFeService([
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

    public function correcaorPorChave(Request $request)
    {
        $chave = $request->chave;
        $correcao = $request->correcao;

        if (strlen($correcao) < 15) {
            return response()->json("Informe a correção com no minimo 15 caracteres", 404);
            die();
        }

        $documento = $this->modelDocumento->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado
            ->where('cnpj', $documento->emitente->cnpj)->first();
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $certificado);

            $res = $nfe_service->cartaCorrecao($documento, $correcao);
            if ($res['retEvento']['infEvento']['xMotivo'] == 'Evento registrado e vinculado a NF-e') {
                $documento->sequencia_correcao = $documento->sequencia_correcao + 1;
                $documento->save();
                return response()->json($res['retEvento'], 200);
            }else{
                return response()->json($res['retEvento']['infEvento'], 401);
            }
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function enviarEmailPorIdDocumento(Request $request){
        $id = $request->documento_id;
        $email = $request->email;

        $documento = $this->modelDocumento->find($id);
        $mail = new PHPMailer(true);

        try {
    //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $mail->isSMTP();                                            
            $mail->Host       = getenv('MAIL_SMTP');                    
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = getenv('MAIL_USER');                     
            $mail->Password   = getenv('MAIL_PASS');                               
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
            $mail->Port       = getenv('MAIL_PORT');  

            $mail->setFrom(getenv('MAIL_USER'), getenv('MAIL_NAME'));
            $mail->addAddress($email, ''); 

            $this->danfeTempEmail($documento);
            if(file_exists("public/xml_nfe/$documento->chave.xml")){
                $mail->addAttachment("public/xml_nfe/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de NFe";
            $mail->Body    = "Olá segue em anexo DANFE e XML NFe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    public function enviarEmailPorChave(Request $request){
        $chave = $request->chave;
        $email = $request->email;

        $documento = $this->modelDocumento->where('chave', $chave)->first();
        $mail = new PHPMailer(true);

        try {
    //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $mail->isSMTP();                                            
            $mail->Host       = getenv('MAIL_SMTP');                    
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = getenv('MAIL_USER');                     
            $mail->Password   = getenv('MAIL_PASS');                               
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
            $mail->Port       = getenv('MAIL_PORT');  

            $mail->setFrom(getenv('MAIL_USER'), getenv('MAIL_NAME'));
            $mail->addAddress($email, ''); 

            $this->danfeTempEmail($documento);
            if(file_exists("public/xml_nfe/$documento->chave.xml")){
                $mail->addAttachment("public/xml_nfe/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de NFe";
            $mail->Body    = "Olá segue em anexo DANFE e XML NFe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    private function danfeTempEmail($documento){
        $xml = file_get_contents('public/xml_nfe/' . $documento->chave . '.xml');
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

        try {
            $danfe = new Danfe($xml);
            $id = $danfe->monta($logo);
            $pdf = $danfe->render();
            file_put_contents('public/email/' . $documento->chave . '.pdf', $pdf);

        } catch (Exception $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }
}
