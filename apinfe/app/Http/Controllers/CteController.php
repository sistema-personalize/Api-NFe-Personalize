<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CTeService;
use App\Models\Certificado;
use App\Models\Cte;
use App\Models\CteChaveNFe;
use App\Models\CteComponente;
use App\Models\CteDestinatario;
use App\Models\CteDocOutros;
use App\Models\CteEmitente;
use App\Models\CteEnderecoTomador;
use App\Models\CteMedida;
use App\Models\CteRemetente;
use Exception;
use NFePHP\DA\CTe\Dacte;
use NFePHP\DA\CTe\Daevento;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class CteController extends Controller
{
    private $modelCertificado;
    private $modelCte;
    private $modelEmitente;
    private $modelRemetente;
    private $modelDestinatario;
    private $modelComponentes;
    private $modelMedidas;
    private $modelCteChave;
    private $modelCteDocOutros;
    private $modelEnderecoTomador;

    public function __construct(
        Certificado $certificado,
        Cte $cte,
        CteChaveNFe $cteChaveNfe,
        CteComponente $cteComponente,
        CteDestinatario $cteDestinatario,
        CteDocOutros $cteDocOutros,
        CteEmitente $cteEmitente,
        CteEnderecoTomador $cteEnderecoTomador,
        CteMedida $cteMedida,
        CteRemetente $cteRemetente
    ) {
        $this->modelCertificado = $certificado;
        $this->modelCte = $cte;
        $this->modelCteChaveNfe = $cteChaveNfe;
        $this->modelCteComponente = $cteComponente;
        $this->modelCteDestinatario = $cteDestinatario;
        $this->modelCteDocOutros = $cteDocOutros;
        $this->modelCteEmitente = $cteEmitente;
        $this->modelCteEnderecoTomador = $cteEnderecoTomador;
        $this->modelCteMedida = $cteMedida;
        $this->modelCteRemetente = $cteRemetente;
    }

    public function all()
    {   
        $docs = $this->modelCte->all();

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $doc->emitente;
                $doc->destinatario;
            }

            return response()->json($docs, 200);
        } else {
            return response()->json("Nenhum documento encontrado", 404);
        }
    }

    public function first($id)
    { // nfe por id
        $doc = $this->modelCte->find($id);
        if ($doc != null) {
            $doc->emitente;
            $doc->destinatario;

            return response()->json($doc, 200);
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function xml($id)
    { // cte por id
        $doc = $this->modelCte->find($id);

        if (file_exists("public/xml_cte/$doc->chave.xml")) {

            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename='.$doc->numero.'.xml');
            header('Pragma: no-cache');
            readfile("public/xml_cte/$doc->chave.xml");

        } else {
            return response()->json('XML não enontrado!', 401);
        }
    }

    public function gerarXml(Request $request)
    {
        $cte = $request->cte;
        $emitente = $request->emitente;
        $enderecoTomador = $request->endereco_tomador;
        $remetente = $request->remetente;
        $destinatario = $request->destinatario;
        $chaves_nfe = $request->chaves_nfe;
        $doc_outros = $request->doc_outros;
        $componentes = $request->componentes;
        $medidas = $request->medidas;

        $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $cte['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $certificado);

        $n = $cte_service->gerar(
            $cte,
            $emitente,
            $enderecoTomador,
            $remetente,
            $destinatario,
            $chaves_nfe,
            $doc_outros,
            $componentes,
            $medidas
        );

        return  response()->json($n['xml'], 201);
    }

    public function transmitir(Request $request)
    {
        $documento = $request->cte;
        $emitente = $request->emitente;
        $enderecoTomador = $request->endereco_tomador;
        $remetente = $request->remetente;
        $destinatario = $request->destinatario;
        $chaves_nfe = $request->chaves_nfe;
        $doc_outros = $request->doc_outros;
        $componentes = $request->componentes;
        $medidas = $request->medidas;

        $certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => $documento['ambiente'],
            "razaosocial" => $emitente['razao_social'],
            "siglaUF" => $emitente['uf'],
            "cnpj" => $emitente['cnpj'],
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $certificado);
        $cte = $cte_service->gerar(
            $documento,
            $emitente,
            $enderecoTomador,
            $remetente,
            $destinatario,
            $chaves_nfe,
            $doc_outros,
            $componentes,
            $medidas
        );

        if(!isset($cte['erros_xml'])){
            $signed = $cte_service->sign($cte['xml']);
            $resultado = $cte_service->transmitir($signed, $cte['chave']);

            if (isset($resultado['protCTe']['infProt']['xMotivo'])) {
                return response()->json($resultado['protCTe']['infProt']['xMotivo'], 401);
            } else if ($resultado == 'ERRO DE COMUNICAÇÃO COM WEBSERVICE SEFAZ') {
                return response()->json($resultado, 401);
            } else {
                try{
                    $documento = $this->criaDocumento($documento, $cte['chave']);
                    $emitente = $this->salvaEmitente($emitente, $documento->id);
                    $remetente = $this->salvaRemetente($remetente, $documento->id);
                    $destinatario= $this->salvaDestinatario($destinatario, $documento->id);
                    $endTomador = $this->salvaEnderecoTomador($enderecoTomador, $documento->id);
                    $chaves = $this->salvaChavesNFe($chaves_nfe, $documento->id);
                    $docOutros = $this->salvaDocOutros($doc_outros, $documento->id);
                    $componentes = $this->salvaComponentes($componentes, $documento->id);
                    $medidas = $this->salvaMedidas($medidas, $documento->id);

                    $doc = $this->modelCte->find($documento->id);

                    $retorno = [
                        'chave' => $cte['chave'],
                        'recibo' => $resultado,
                        'documento_id' => $doc->id
                    ];

                    return response()->json($retorno, 201);
                }catch(\Exception $e){
                    return response()->json("Documento gerado com erro ao armazenar no banco: " . $e->getMessage(), 401);
                }
            }
        }else{
            return response()->json($cte['erros_xml'], 401);
        }
    }


    private function criaDocumento($cte, $chave, $estado = 'APROVADO')
    {
        $arr = [
            'natureza_operacao' => $cte['natureza_operacao'],
            'numero' => $cte['numero'],
            'ambiente' => $cte['ambiente'],
            'cfop' => $cte['cfop'],
            'codigo_mun_envio' => $cte['codigo_mun_envio'],
            'nome_municipio_envio' => $cte['nome_municipio_envio'],
            'uf_municipio_envio' => $cte['uf_municipio_envio'],
            'codigo_municipio_inicio' => $cte['codigo_municipio_inicio'],
            'nome_municipio_inicio' => $cte['nome_municipio_inicio'],
            'uf_municipio_inicio' => $cte['uf_municipio_inicio'], 
            'codigo_municipio_fim' => $cte['codigo_municipio_fim'],
            'nome_municipio_fim' => $cte['nome_municipio_fim'],
            'uf_municipio_fim' => $cte['uf_municipio_fim'],
            'modal' => $cte['modal'],
            'retira' => $cte['retira'],
            'detalhes_retira' => $cte['detalhes_retira'],
            'tomador' => $cte['tomador'],
            'cst' => $cte['cst'],
            'perc_icms' => $cte['perc_icms'],
            'data_prevista_entrega' => $cte['data_prevista_entrega'],
            'valor_transporte' => $cte['valor_transporte'],
            'valor_receber' => $cte['valor_receber'],
            'produto_predominante' => $cte['produto_predominante'],
            'valor_carga' => $cte['valor_carga'],
            'rntrc' => $cte['rntrc'],
            'chave' => $chave,
            'estado' => $estado,
            'sequencia_correcao' => 0
        ];
        $res = $this->modelCte->create($arr);
        return $res;
    }

    private function salvaEmitente($emitente, $cte_id)
    {
        $arr = [
            'razao_social' => $emitente['razao_social'],
            'nome_fantasia' => $emitente['nome_fantasia'],
            'ie' => $emitente['ie'],
            'cnpj' => $emitente['cnpj'],
            'logradouro' => $emitente['logradouro'],
            'numero' => $emitente['numero'],
            'complemento' => $emitente['complemento'],
            'bairro' => $emitente['bairro'],
            'nome_municipio' => $emitente['nome_municipio'],
            'cod_municipio_ibge' => $emitente['cod_municipio_ibge'],
            'uf' => $emitente['uf'],
            'cep' => $emitente['cep'],
            'telefone' => $emitente['telefone'],
            'cte_id' => $cte_id
        ];
        $this->modelCteEmitente->create($arr);
    }

    private function salvaRemetente($remetente, $cte_id)
    {
        $arr = [
            'cnpj' => $remetente['cnpj'],
            'ie' => $remetente['ie'],
            'razao_social' => $remetente['razao_social'],
            'nome_fantasia' => $remetente['nome_fantasia'],
            'fone' => $remetente['fone'],
            'email' => $remetente['email'],
            'logradouro' => $remetente['logradouro'],
            'numero' => $remetente['numero'],
            'bairro' => $remetente['bairro'],
            'complemento' => $remetente['complemento'],
            'nome_municipio' => $remetente['nome_municipio'],
            'codigo_municipio_ibge' => $remetente['codigo_municipio_ibge'],
            'cep' => $remetente['cep'],
            'uf' => $remetente['uf'],
            'cte_id' => $cte_id
        ];
        $this->modelCteRemetente->create($arr);
    }

    private function salvaDestinatario($destinatario, $cte_id)
    {
        $arr = [
            'cpf_cnpj' => $destinatario['cpf_cnpj'],
            'ie_rg' => $destinatario['ie_rg'],
            'razao_social' => $destinatario['razao_social'],
            'nome_fantasia' => $destinatario['nome_fantasia'],
            'fone' => $destinatario['fone'],
            'email' => $destinatario['email'],
            'logradouro' => $destinatario['logradouro'],
            'numero' => $destinatario['numero'],
            'bairro' => $destinatario['bairro'],
            'complemento' => $destinatario['complemento'],
            'nome_municipio' => $destinatario['nome_municipio'],
            'codigo_municipio_ibge' => $destinatario['codigo_municipio_ibge'],
            'cep' => $destinatario['cep'],
            'uf' => $destinatario['uf'],
            'cte_id' => $cte_id
        ];
        $this->modelCteDestinatario->create($arr);
    }

    private function salvaEnderecoTomador($enderecoTomador, $cte_id)
    {
        $arr = [
            'logradouro' => $enderecoTomador['logradouro'],
            'numero' => $enderecoTomador['numero'],
            'bairro' => $enderecoTomador['bairro'],
            'complemento' => $enderecoTomador['complemento'],
            'codigo_municipio_ibge' => $enderecoTomador['codigo_municipio_ibge'],
            'nome_municipio' => $enderecoTomador['nome_municipio'], 
            'cep' => $enderecoTomador['cep'],
            'uf' => $enderecoTomador['uf'],
            'cte_id' => $cte_id
        ];
        $this->modelCteEnderecoTomador->create($arr);
    }

    private function salvaChavesNFe($chaves_nfe, $cte_id)
    {
        if(isset($chaves_nfe) && sizeof($chaves_nfe) > 0){
            foreach($chaves_nfe as $c){
                $arr = [
                    'chave' => $c['chave'], 
                    'cte_id' => $cte_id
                ];
                $this->modelCteChaveNfe->create($arr);
            }
        }
    }

    private function salvaDocOutros($doc_outros, $cte_id)
    {
        if(isset($doc_outros) && sizeof($doc_outros) > 0){
            foreach($doc_outros as $doc){
                $arr = [
                    'tipo' => $doc['tipo'],
                    'descricao' => $doc['descricao'],
                    'numero' => $doc['numero'],
                    'data_emissao' => $doc['data_emissao'],
                    'valor' => $doc['valor'],
                    'cte_id' => $cte_id
                ];
                $this->modelCteDocOutros->create($arr);
            }
        }
    }

    private function salvaComponentes($componentes, $cte_id)
    {
        if(isset($componentes) && sizeof($componentes) > 0){
            foreach($componentes as $c){
                $arr = [
                    'nome' => $c['nome'],
                    'valor' => $c['valor'],
                    'cte_id' => $cte_id
                ];
                $this->modelCteComponente->create($arr);
            }
        }
    }

    private function salvaMedidas($medidas, $cte_id)
    {
        if(isset($medidas) && sizeof($medidas) > 0){
            foreach($medidas as $m){
                $arr = [
                    'cod_unidade' => $m['cod_unidade'],
                    'tipo_medida' => $m['tipo_medida'],
                    'quantidade_carga' => $m['quantidade_carga'],
                    'cte_id' => $cte_id 
                ];
                $this->modelCteMedida->create($arr);
            }
        }
    }

    //impressão
    public function imprimirPorDocumento($id)
    {
        $documento = $this->modelCte->find($id);

        if ($documento != null) {
            $xml = file_get_contents('public/xml_cte/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dacte = new Dacte($xml);
                $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $dacte->monta($logo);
                $pdf = $dacte->render();
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
        $documento = $this->modelCte->where('chave', $chave)->first();

        if ($documento != null) {
            $xml = file_get_contents('public/xml_cte/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dacte = new Dacte($xml);
                $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $dacte->monta($logo);
                $pdf = $dacte->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    //consulta

    public function consultarPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $documento = $this->modelCte->find($id);

        if ($documento != null) {

            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();

            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->consultar($documento);
            return response()->json($res['protCTe']['infProt'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultarPorChave(Request $request)
    {
        $chave = $request->chave;
        $documento = $this->modelCte->where('chave', $chave)->first();

        if ($documento != null) {

            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();

            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->consultar($documento);
            return response()->json($res['protCTe']['infProt'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    //correção

    public function correcaoPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $valor_alterar = $request->valor_alterar;
        $grupo = $request->grupo;
        $campo = $request->campo;

        $documento = $this->modelCte->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->cartaCorrecao($documento, $grupo, $campo, $valor_alterar);

            if ($res['infEvento']['xMotivo'] == 'Evento registrado e vinculado a CT-e') {
                $documento->sequencia_correcao = $documento->sequencia_correcao + 1;
                $documento->save();
                return response()->json($res['infEvento'], 200);

            }else{
                return response()->json($res['infEvento']['xMotivo'], 401);
            }
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function correcaorPorChave(Request $request)
    {
        $chave = $request->chave;
        $valor_alterar = $request->valor_alterar;
        $grupo = $request->grupo;
        $campo = $request->campo;

        $documento = $this->modelCte->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->cartaCorrecao($documento, $grupo, $campo, $valor_alterar);

            if ($res['infEvento']['xMotivo'] == 'Evento registrado e vinculado a CT-e') {
                $documento->sequencia_correcao = $documento->sequencia_correcao + 1;
                $documento->save();
                return response()->json($res['infEvento'], 200);

            }else{
                return response()->json($res['infEvento']['xMotivo'], 401);
            }
        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    //cancelar

    public function cancelarPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $justificativa = $request->justificativa;

        if (strlen($justificativa) < 15) {
            return response()->json("Informe a justificativa com no minimo 15 caracteres", 404);
            die();
        }

        $documento = $this->modelCte->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->cancelar($documento, $justificativa);

            if ($res['infEvento']['xMotivo'] == 'Evento registrado e vinculado a CT-e') {
                $documento->estado = 'CANCELADO';
                $documento->save();
                return response()->json($res['infEvento'], 200);
            }else{
                return response()->json($res['infEvento']['xMotivo'], 401);
            }

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

        $documento = $this->modelCte->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => $documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $certificado);

            $res = $cte_service->cancelar($documento, $justificativa);

            if ($res['infEvento']['xMotivo'] == 'Evento registrado e vinculado a CT-e') {
                $documento->estado = 'CANCELADO';
                $documento->save();
                return response()->json($res['infEvento'], 200);
            }else{
                return response()->json($res['infEvento']['xMotivo'], 401);
            }

        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

     //impressão cancela
    public function imprimirCancelamentoPorDocumento($id)
    {
        $documento = $this->modelCte->find($id);

        if ($documento != null) {

            if($documento->estado != 'CANCELADO'){
                return response()->json("Este documento não está cancelado", 404); 
            }

            $xml = file_get_contents('public/xml_cte_cancelada/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dadosEmitente = $this->getEmitente($documento);

                $daevento = new Daevento($xml, $dadosEmitente);
                // $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCancelamentoPorChave($chave)
    {
        $documento = $this->modelCte->where('chave', $chave)->first();

        if ($documento != null) {

            if($documento->estado != 'CANCELADO'){
                return response()->json("Este documento não está cancelado", 404); 
            }

            $xml = file_get_contents('public/xml_cte_cancelada/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dadosEmitente = $this->getEmitente($documento);

                $daevento = new Daevento($xml, $dadosEmitente);
                // $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    private function getEmitente($documento){
        return [
            'razao' => $documento->emitente->razao_social,
            'logradouro' => $documento->emitente->logradouro,
            'numero' => $documento->emitente->numero,
            'complemento' => $documento->emitente->complemento,
            'bairro' => $documento->emitente->bairro,
            'CEP' => $documento->emitente->cep,
            'municipio' => $documento->emitente->nome_municipio,
            'UF' => $documento->emitente->uf,
            'telefone' => $documento->emitente->telefone,
            'email' => $documento->emitente->email
        ];
    }

    //impressão corricao
    public function imprimirCorrecaoPorDocumento($id)
    {
        $documento = $this->modelCte->find($id);

        if ($documento != null) {

            if($documento->sequencia_correcao == 0){
                return response()->json("Este documento não está corrigido", 404); 
            }

            $xml = file_get_contents('public/xml_cte_correcao/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dadosEmitente = $this->getEmitente($documento);

                $daevento = new Daevento($xml, $dadosEmitente);
                // $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function imprimirCorrecaoPorChave($chave)
    {
        $documento = $this->modelCte->where('chave', $chave)->first();

        if ($documento != null) {

            if($documento->sequencia_correcao == 0){
                return response()->json("Este documento não está corrigido", 404); 
            }

            $xml = file_get_contents('public/xml_cte_correcao/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $dadosEmitente = $this->getEmitente($documento);

                $daevento = new Daevento($xml, $dadosEmitente);
                // $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento: " . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
        }
    }

    public function enviarEmailPorIdDocumento(Request $request){
        $id = $request->documento_id;
        $email = $request->email;

        $documento = $this->modelCte->find($id);
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
            if(file_exists("public/xml_cte/$documento->chave.xml")){
                $mail->addAttachment("public/xml_cte/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de CTe";
            $mail->Body    = "Olá segue em anexo DAMCTE e XML CTe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    public function enviarEmailPorChave(Request $request){
        $chave = $request->chave;
        $email = $request->email;

        $documento = $this->modelCte->where('chave', $chave)->first();
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
            if(file_exists("public/xml_cte/$documento->chave.xml")){
                $mail->addAttachment("public/xml_cte/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de CTe";
            $mail->Body    = "Olá segue em anexo DAMCTE e XML CTe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    private function danfeTempEmail($documento){
        $xml = file_get_contents('public/xml_cte/' . $documento->chave . '.xml');
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

        try {
            $dacte = new Dacte($xml);
            $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
            $dacte->monta($logo);
            $pdf = $dacte->render();

            file_put_contents('public/email/' . $documento->chave . '.pdf', $pdf);

        } catch (Exception $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }



}
