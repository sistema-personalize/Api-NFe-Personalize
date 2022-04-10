<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MDFeService;
use App\Models\Certificado;
use App\Models\Mdfe;
use App\Models\MdfeChaveCte;
use App\Models\MdfeChaveNfe;
use App\Models\MdfeEmitente;
use App\Models\MdfeInfoDescarga;
use App\Models\MdfeLacresTranporte;
use App\Models\MdfeLacresUnidadeCarga;
use App\Models\MdfeMunicipioCarregamento;
use App\Models\MdfePercurso;
use App\Models\MdfeSeguradora;
use App\Models\MdfeValePedagio;
use App\Models\MdfeVeiculo;
use App\Models\MdfeCiot;

use Exception;
use NFePHP\DA\MDFe\Damdfe;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MdfeController extends Controller
{
	private $modelCertificado;
	private $modelMdfe;
	private $modelMdfeChaveCte;
	private $modelMdfeChaveNfe;
	private $modelMdfeEmitente;
	private $modelMdfeInfoDescarga;
	private $modelMdfeLacreTransporte;
	private $modelMdfeLacreUnidade;
	private $modelMdfeMunicipioCarregamento;
	private $modelMdfePercurso;
	private $modelMdfeSeguradora;
	private $modelMdfePedagio;
	private $modelMdfeVeiculo;
	private $modelMdfeCiot;

	public function __construct(
		Certificado $certificado,
		Mdfe $mdfe,
		MdfeChaveCte $modelChaveCte,
		MdfeChaveNfe $mdfeChaveNfe,
		MdfeEmitente $mdfeEmitente,
		MdfeInfoDescarga $mdfeInfoDescarga,
		MdfeLacresTranporte $mdfeLacresTranporte,
		MdfeLacresUnidadeCarga $mdfeLacresUnidadeCarga,
		MdfeMunicipioCarregamento $mdfeMunicipioCarregamento,
		MdfePercurso $mdfePercurso,
		MdfeSeguradora $mdfeSeguradora,
		MdfeValePedagio $mdfeValePedagio,
		MdfeVeiculo $mdfeVeiculo,
		MdfeCiot $mdfeCiot
	) {
		$this->modelCertificado = $certificado;
		$this->modelMdfe = $mdfe;
		$this->modelMdfeChaveCte = $modelChaveCte;
		$this->modelMdfeChaveNfe = $mdfeChaveNfe;
		$this->modelMdfeEmitente = $mdfeEmitente;
		$this->modelMdfeInfoDescarga = $mdfeInfoDescarga;
		$this->modelMdfeLacreTransporte = $mdfeLacresTranporte;
		$this->modelMdfeLacreUnidade = $mdfeLacresUnidadeCarga;
		$this->modelMdfeMunicipioCarregamento = $mdfeMunicipioCarregamento;
		$this->modelMdfePercurso = $mdfePercurso;
		$this->modelMdfeSeguradora = $mdfeSeguradora;
		$this->modelMdfePedagio = $mdfeValePedagio;
		$this->modelMdfeVeiculo = $mdfeVeiculo;
		$this->modelMdfeCiot = $mdfeCiot;
	}

	public function all()
	{   
		$docs = $this->modelMdfe->all();

		if (!empty($docs)) {
			foreach ($docs as $doc) {
				$doc->emitente;
			}

			return response()->json($docs, 200);
		} else {
			return response()->json("Nenhum documento encontrado", 404);
		}
	}

	public function first($id)
    { // nfe por id
    	$doc = $this->modelMdfe->find($id);
    	if ($doc != null) {
    		$doc->emitente;

    		return response()->json($doc, 200);
    	} else {
    		return response()->json("Documento não encontrado", 404);
    	}
    }

    public function gerarXml(Request $request)
    {
    	$mdfe = $request->mdfe;
    	$emitente = $request->emitente;
    	$municipios_carregamento = $request->municipios_carregamento;
    	$percurso = $request->percurso;
    	$vale_pedagio = $request->vale_pedagio;
    	$veiculos = $request->veiculos;
    	$seguradora = $request->seguradora;
    	$info_descarregamento = $request->info_descarregamento;
    	$ciots = $request->ciots;

    	$certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

    	$mdfe_service = new MDFeService([
    		"atualizacao" => date('Y-m-d h:i:s'),
    		"tpAmb" => (int)$mdfe['ambiente'],
    		"razaosocial" => $emitente['razao_social'],
    		"siglaUF" => $emitente['uf'],
    		"cnpj" => $emitente['cnpj'],
    		"inscricaomunicipal" => $emitente['inscricao_municipal'],
    		"codigomunicipio" => $emitente['cod_municipio_ibge'],
    		"schemes" => "PL_MDFe_300a",
    		"versao" => '3.00'
    	], $certificado);

    	$xml = $mdfe_service->gerar(
    		$mdfe,
    		$emitente,
    		$municipios_carregamento,
    		$percurso,
    		$vale_pedagio,
    		$veiculos,
    		$seguradora,
    		$info_descarregamento,
    		$ciots
    	);
    	if(!isset($xml['erros_xml'])){
    		return  response()->json($xml['xml'], 201);
    	}else{

    		return response()->json($xml, 401);
    	}

    }

    public function transmitir(Request $request)
    {
    	$mdfe = $request->mdfe;
    	$emitente = $request->emitente;
    	$municipios_carregamento = $request->municipios_carregamento;
    	$percurso = $request->percurso;
    	$vale_pedagio = $request->vale_pedagio;
    	$veiculos = $request->veiculos;
    	$seguradora = $request->seguradora;
    	$info_descarregamento = $request->info_descarregamento;
    	$ciots = $request->ciots;

    	$certificado = $this->modelCertificado->where('cnpj', $emitente['cnpj'])->first();

    	$mdfe_service = new MDFeService([
    		"atualizacao" => date('Y-m-d h:i:s'),
    		"tpAmb" => (int)$mdfe['ambiente'],
    		"razaosocial" => $emitente['razao_social'],
    		"siglaUF" => $emitente['uf'],
    		"cnpj" => $emitente['cnpj'],
    		"inscricaomunicipal" => $emitente['inscricao_municipal'],
    		"codigomunicipio" => $emitente['cod_municipio_ibge'],
    		"schemes" => "PL_MDFe_300a",
    		"versao" => '3.00'
    	], $certificado);

    	$xml = $mdfe_service->gerar(
    		$mdfe,
    		$emitente,
    		$municipios_carregamento,
    		$percurso,
    		$vale_pedagio,
    		$veiculos,
    		$seguradora,
    		$info_descarregamento,
    		$ciots
    	);
    	if(!isset($xml['erros_xml'])){

    		$signed = $mdfe_service->sign($xml['xml']);
    		$resultado = $mdfe_service->transmitir($signed);
    		if(!isset($resultado['erro'])){
    			try{
    				$documento = $this->criaDocumento($mdfe, $resultado['chave'], 
    					$resultado['protocolo']);
    				$emitente = $this->salvaEmitente($emitente, $documento->id);
    				$municipios_carregamento = $this->salvaMunicipiosCarregamento($municipios_carregamento, $documento->id);
                    $percurso = $this->salvaPercurso($percurso, $documento->id);
                    $vale_pedagio = $this->salvaValePedagio($vale_pedagio, $documento->id);
                    $veiculos = $this->salvaVeiculos($veiculos, $documento->id);
                    $seguradora = $this->salvaSeguradora($seguradora, $documento->id);
                    $ciots = $this->salvaCiots($ciots, $documento->id);
                    $info_descarregamento = $this->salvaInfos($info_descarregamento, $documento->id);
    				// $destinatario= $this->salvaDestinatario($destinatario, $documento->id);
    				// $endTomador = $this->salvaEnderecoTomador($enderecoTomador, $documento->id);
    				// $chaves = $this->salvaChavesNFe($chaves_nfe, $documento->id);
    				// $docOutros = $this->salvaDocOutros($doc_outros, $documento->id);
    				// $componentes = $this->salvaComponentes($componentes, $documento->id);
    				// $medidas = $this->salvaMedidas($medidas, $documento->id);

                    $doc = $this->modelMdfe->find($documento->id);

                    $retorno = [
                        'chave' => $resultado['chave'],
                        'protocolo' => $resultado['protocolo'],
                        'documento_id' => $doc->id
                    ];

                    return response()->json($retorno, 201);
                }catch(\Exception $e){
                    return response()->json("Documento gerado com erro ao armazenar no banco: " . $e->getMessage(), 401);
                }
            }else{
                return response()->json($resultado['message'], 403);
            }

        }else{
            return response()->json($xml, 401);
        }

    }

    private function criaDocumento($mdfe, $chave, $protocolo, $estado = 'APROVADO')
    {
        $arr = [
            'chave' => $chave,
            'estado' => $estado,
            'numero' => $mdfe['numero'],
            'protocolo' => $protocolo,
            'ambiente' => $mdfe['ambiente'],
            'uf_inicio' => $mdfe['uf_inicio'],
            'uf_fim' => $mdfe['uf_fim'],
            'data_inicio_viagem' => $mdfe['data_inicio_viagem'], 
            'carga_posterior' => $mdfe['carga_posterior'],
            'cnpj_contratante' => $mdfe['cnpj_contratante'],
            'valor_carga' => $mdfe['valor_carga'],
            'quantidade_carga' => $mdfe['quantidade_carga'],
            'info_complementar' => $mdfe['info_complementar'], 
            'info_adicional_fisco' => $mdfe['info_adicional_fisco'],
            'condutor_nome' => $mdfe['condutor_nome'],
            'condutor_cpf' => $mdfe['condutor_cpf'],
            'lacre_rodoviario' => $mdfe['lacre_rodoviario'],
            'tipo_emitente' => $mdfe['tipo_emitente'],
            'tipo_transporte' => $mdfe['tipo_transporte']
        ];
        $res = $this->modelMdfe->create($arr);
        return $res;
    }

    private function salvaEmitente($emitente, $mdfe_id)
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
            'mdfe_id' => $mdfe_id,
            'inscricao_municipal' => $emitente['inscricao_municipal']
        ];
        $this->modelMdfeEmitente->create($arr);
    }

    private function salvaMunicipiosCarregamento($municipios_carregamento, $mdfe_id)
    {
        foreach($municipios_carregamento as $m){
            $arr = [
                'nome' => $m['nome'],
                'codigo_municipio_ibge' => $m['codigo_municipio_ibge'],
                'mdfe_id' => $mdfe_id
            ];

            $this->modelMdfeMunicipioCarregamento->create($arr);
        }

    }

    private function salvaPercurso($percurso, $mdfe_id)
    {
        if($percurso){
            foreach($percurso as $p){
                $arr = [
                    'nome' => $p['uf'],
                    'mdfe_id' => $mdfe_id
                ];

                $this->modelMdfePercurso->create($arr);
            }
        }

    }

    private function salvaValePedagio($valePedagio, $mdfe_id)
    {
        if($valePedagio){
            foreach($valePedagio as $v){
                $arr = [
                    'cnpj_contratante' => $v['cnpj_contratante'],
                    'cnpj_fornecedor_pagador' => $v['cnpj_fornecedor_pagador'],
                    'numero_compra' => $v['numero_compra'],
                    'valor' => $v['valor'],
                    'mdfe_id' => $mdfe_id
                ];

                $this->modelMdfePedagio->create($arr);
            }
        }
    }

    private function salvaVeiculos($veiculos, $mdfe_id)
    {
        if($veiculos){
            foreach($veiculos as $v){
                $arr = [
                    'rntrc' => $v['rntrc'],
                    'placa' => $v['placa'],
                    'tara' => $v['tara'],
                    'capacidade' => $v['capacidade'],
                    'tipo_rodado' => $v['tipo_rodado'],
                    'tipo_carroceira' => $v['tipo_carroceira'],
                    'uf' => $v['uf'],
                    'nome_proprietario' => $v['nome_proprietario'],
                    'cpf_cnpj_proprietario' => $v['cpf_cnpj_proprietario'],
                    'ie_proprietario' => $v['ie_proprietario'],
                    'tipo_proprietario' => $v['tipo_proprietario'], 
                    'uf_proprietario' => $v['uf_proprietario'],
                    'tipo_veiculo' => $v['tipo_veiculo'],
                    'mdfe_id' => $mdfe_id
                ];

                $this->modelMdfeVeiculo->create($arr);
            }
        }
    }

    private function salvaSeguradora($seguradora, $mdfe_id)
    {
        if($seguradora){
            $arr = [
                'nome' => $seguradora['nome'],
                'cnpj' => $seguradora['cnpj'],
                'numero_apolice' => $seguradora['numero_apolice'],
                'numero_averbacao' => $seguradora['numero_averbacao'],
                'mdfe_id' => $mdfe_id
            ];

            $this->modelMdfeSeguradora->create($arr);
            
        }
    }

    private function salvaCiots($ciots, $mdfe_id)
    {
        if($ciots){
            foreach($ciots as $c){
                $arr = [
                    'codigo' => $c['codigo'],
                    'cpf_cnpj' => $c['cpf_cnpj'],
                    'mdfe_id' => $mdfe_id
                ];

                $this->modelMdfeCiot->create($arr);
            }
        }
    }

    private function salvaInfos($infos, $mdfe_id)
    {
        if($infos){
            foreach($infos as $c){
                $arr = [
                    'nome_municipio' => $c['nome_municipio'],
                    'cod_municipio_ibge' => $c['cod_municipio_ibge'],
                    'id_unidade_carga' => $c['id_unidade_carga'],
                    'quantidade_rateio' => $c['quantidade_rateio'], 
                    'tipo_unidade_transporte' => $c['tipo_unidade_transporte'],
                    'id_unidade_transporte' => $c['id_unidade_transporte'], 
                    'mdfe_id' => $mdfe_id
                ];

                $this->modelMdfeInfoDescarga->create($arr);
            }
        }
    }

    // FIM BD

    public function documentosNaoEncerrados(Request $request){
        $certificado = $this->modelCertificado->where('cnpj', $request['cnpj'])->first();

        $mdfe_service = new MDFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$request['ambiente'],
            "razaosocial" => $request['razao_social'],
            "siglaUF" => $request['uf'],
            "cnpj" => $request['cnpj'],
            "inscricaomunicipal" => $request['inscricao_municipal'],
            "codigomunicipio" => $request['cod_municipio_ibge'],
            "schemes" => "PL_MDFe_300a",
            "versao" => '3.00'
        ], $certificado);

        $resultados = $mdfe_service->naoEncerrados();
        $naoEncerrados = [];

        if($resultados['xMotivo'] != 'Consulta não encerrados não localizou MDF-e nessa situação'){
            if(sizeof($resultados['infMDFe']) > 1){
                $array = [
                    'chave' => $resultados['infMDFe']['chMDFe'],
                    'protocolo' => $resultados['infMDFe']['nProt']
                ];
                array_push($naoEncerrados, $array);

            }else{

                foreach($resultados['infMDFe'] as $inf){

                    $array = [
                        'chave' => $inf['chMDFe'],
                        'protocolo' => $inf['nProt']
                    ];
                    array_push($naoEncerrados, $array);

                }
            }
        }
        return $naoEncerrados;
    }


    public function encerrar(Request $request){
        $certificado = $this->modelCertificado->where('cnpj', $request['cnpj'])->first();

        $mdfe_service = new MDFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$request['ambiente'],
            "razaosocial" => $request['razao_social'],
            "siglaUF" => $request['uf'],
            "cnpj" => $request['cnpj'],
            "inscricaomunicipal" => $request['inscricao_municipal'],
            "codigomunicipio" => $request['cod_municipio_ibge'],
            "schemes" => "PL_MDFe_300a",
            "versao" => '3.00'
        ], $certificado);

        foreach($request['documentos'] as $d){
            try {
                $std = $mdfe_service->encerrar($d['chave'], $d['protocolo'], 
                    Mdfe::getCodUF($request['uf']), $request['cod_municipio_ibge']);
                return response()->json($std, 200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 401);
            }

        }

        return response()->json(true, 200);

    }

    public function consultarPorIdDocumento(Request $request)
    {
        $id = $request->documento_id;
        $documento = $this->modelMdfe->find($id);

        if ($documento != null) {

            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();

            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "inscricaomunicipal" => $documento->emitente->inscricao_municipal,
                "codigomunicipio" => $documento->emitente->cod_municipio_ibge,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $certificado);

            $res = $mdfe_service->consultar($documento->chave);
            return response()->json($res['protMDFe'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultarPorChave(Request $request)
    {
        $chave = $request->chave;
        $documento = $this->modelMdfe->where('chave', $chave)->first();

        if ($documento != null) {

            $certificado = $this->modelCertificado->
            where('cnpj', $documento->emitente->cnpj)->first();

            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "inscricaomunicipal" => $documento->emitente->inscricao_municipal,
                "codigomunicipio" => $documento->emitente->cod_municipio_ibge,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $certificado);

            $res = $mdfe_service->consultar($documento->chave);
            return response()->json($res['protMDFe'], 200);
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    //impressão
    public function imprimirPorDocumento($id)
    {
        $documento = $this->modelMdfe->find($id);

        if ($documento != null) {

            $xml = file_get_contents('public/xml_mdfe/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $damdfe = new Damdfe($xml);
                $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $damdfe->monta($logo);
                $pdf = $damdfe->render();
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
        $documento = $this->modelMdfe->where('chave', $chave)->first();

        if ($documento != null) {
            $xml = file_get_contents('public/xml_mdfe/' . $documento->chave . '.xml');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

            try {
                $damdfe = new Damdfe($xml);
                $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                $damdfe->monta($logo);
                $pdf = $damdfe->render();
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            return response()->json('Documento não encontrado!', 404);
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

        $documento = $this->modelMdfe->find($id);
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "inscricaomunicipal" => $documento->emitente->inscricao_municipal,
                "codigomunicipio" => $documento->emitente->cod_municipio_ibge,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $certificado);


            $result = $mdfe_service->cancelar($documento->chave, $documento->protocolo, 
             $justificativa);

            if($result['infEvento']['cStat'] == '101' || $result['infEvento']['cStat'] == '135' || $result['infEvento']['cStat'] == '155'){
                $documento->estado = 'CANCELADO';
                $documento->save();
                return response()->json($result, 200);

            }else{
                return response()->json($result['infEvento']['xMotivo'], 401);
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

        $documento = $this->modelMdfe->where('chave', $chave)->first();
        if ($documento != null) {
            $certificado = $this->modelCertificado->where('cnpj', $documento->emitente->cnpj)->first();
            $mdfe_service = new MDFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$documento->ambiente,
                "razaosocial" => $documento->emitente->razao_social,
                "siglaUF" => $documento->emitente->uf,
                "cnpj" => $documento->emitente->cnpj,
                "inscricaomunicipal" => $documento->emitente->inscricao_municipal,
                "codigomunicipio" => $documento->emitente->cod_municipio_ibge,
                "schemes" => "PL_MDFe_300a",
                "versao" => '3.00'
            ], $certificado);


            $result = $mdfe_service->cancelar($documento->chave, $documento->protocolo, 
                $justificativa);

            if($result['infEvento']['cStat'] == '101' || $result['infEvento']['cStat'] == '135' || $result['infEvento']['cStat'] == '155'){
                $documento->estado = 'CANCELADO';
                $documento->save();
                return response()->json($result, 200);

            }else{
                return response()->json($result['infEvento']['xMotivo'], 401);
            }

        } else {
            return response()->json("Documento não encontrado", 404);
        }
    }

    public function enviarEmailPorIdDocumento(Request $request){
        $id = $request->documento_id;
        $email = $request->email;

        $documento = $this->modelMdfe->find($id);
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
            if(file_exists("public/xml_mdfe/$documento->chave.xml")){
                $mail->addAttachment("public/xml_mdfe/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de MDFE";
            $mail->Body    = "Olá segue em anexo DAMDFE e XML MDFe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    public function enviarEmailPorChave(Request $request){
        $chave = $request->chave;
        $email = $request->email;

        $documento = $this->modelMdfe->where('chave', $chave)->first();
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
            if(file_exists("public/xml_mdfe/$documento->chave.xml")){
                $mail->addAttachment("public/xml_mdfe/$documento->chave.xml");
            }
            if(file_exists("public/email/$documento->chave.pdf")){
                $mail->addAttachment("public/email/$documento->chave.pdf");
            }

            $mail->isHTML(true);                                
            $mail->Subject = "Envio Automatico de MDFe";
            $mail->Body    = "Olá segue em anexo DAMDFE e XML MDFe $documento->numero_nf";
            $mail->send();
            return response()->json("Email enviado", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }

    private function danfeTempEmail($documento){
        $xml = file_get_contents('public/xml_mdfe/' . $documento->chave . '.xml');
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents('public/imgs/'.$documento->emitente->cnpj.'.jpg'));

        try {
            $damdfe = new Damdfe($xml);
            $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
            $damdfe->monta($logo);
            $pdf = $damdfe->render();

            file_put_contents('public/email/' . $documento->chave . '.pdf', $pdf);

        } catch (Exception $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

}