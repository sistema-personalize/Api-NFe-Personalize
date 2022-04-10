<?php


namespace App\Services;

use NFePHP\MDFe\Make;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Mdfe;
use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class MDFeService{

	private $config; 

	public function __construct($config, $certificado){
		$this->config = $config;
		$certificadoDigital = $certificado->arquivo;
		if($certificadoDigital == null){
			$certificadoDigital = file_get_contents("public/certificados/".$certificado->path_arquivo);
		}
		$senha = $certificado->senha;

		$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificadoDigital, $senha));
	}

	public function gerar(
		$mdfe,
		$emitente,
		$municipios_carregamento,
		$percurso,
		$vale_pedagio,
		$veiculos,
		$seguradora,
		$info_descarregamento,
		$ciots
	){
		$mdfex = new Make();
		$mdfex->setOnlyAscii(true);

		$std = new \stdClass();
		$std->cUF = Mdfe::getCodUF($emitente['uf']);
		$std->tpAmb = (int)$mdfe['ambiente'];
		$std->tpEmit = $mdfe['tipo_emitente']; 
		$std->tpTransp = $mdfe['tipo_transporte']; 

		$std->mod = '58';
		$std->serie = '0';

		$mdfeLast = $mdfe['numero'];

		$std->nMDF = $mdfeLast; // ver aqui
		$std->cMDF = rand(11111111, 99999999);
		$std->cDV = '5';
		$std->modal = '1';
		$std->dhEmi = date("Y-m-d\TH:i:sP");
		$std->tpEmis = '1';
		$std->procEmi = '0';
		$std->verProc = '1.6';
		$std->UFIni = $mdfe['uf_inicio'];
		$std->UFFim = $mdfe['uf_fim'];
		$std->dhIniViagem = $mdfe['data_inicio_viagem'] . 'T06:00:48-03:00';
		// $std->indCanalVerde = '1';
		// $std->indCarregaPosterior = $mdfe->carga_posterior;
		$mdfex->tagide($std);

		foreach($municipios_carregamento as $m){
			$infMunCarrega = new \stdClass();
			$infMunCarrega->cMunCarrega = $m['codigo_municipio_ibge'];
			$infMunCarrega->xMunCarrega = $m['nome'];
			$mdfex->taginfMunCarrega($infMunCarrega);
		}

		if($percurso){
			foreach($percurso as $p){
				$infPercurso = new \stdClass();
				$infPercurso->UFPer = $p['uf'];
				$mdfex->taginfPercurso($infPercurso);
			}
		}

		$std = new \stdClass();

		$cnpj = $emitente['cnpj'];
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace(".", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$std->CNPJ = str_replace(" ", "", $cnpj);
		$std->IE = $emitente['ie'];
		$std->xNome = $emitente['razao_social'];
		$std->xFant = $emitente['nome_fantasia'];
		$mdfex->tagemit($std);

		$std = new \stdClass();
		$std->xLgr = $emitente['logradouro'];
		$std->nro = $emitente['numero'];
		$std->xBairro = $emitente['bairro'];
		$std->cMun = $emitente['cod_municipio_ibge'];
		$std->xMun = $emitente['nome_municipio'];
		$cep = str_replace("-", "", $emitente['cep']);
		$cep = str_replace(".", "", $cep);
		$std->CEP = $cep;
		$std->UF = $emitente['uf'];
		$std->fone = $emitente['telefone'];
		$std->email = '';
		$mdfex->tagenderEmit($std);


		/* Grupo infANTT */

		$veiculoTracao = null;
		foreach($veiculos as $v){
			if($v['tipo_veiculo'] == 't') $veiculoTracao = $v;
		}
		$infANTT = new \stdClass();
		$infANTT->RNTRC = $veiculoTracao['rntrc']; // pega antt do veiculo de tracao
		$mdfex->taginfANTT($infANTT);

		if($ciots){
			foreach($ciots as $c){
				$infCIOT = new \stdClass();
				$infCIOT->CIOT = $c['codigo'];

				$doc = str_replace("-", "", $c['cpf_cnpj']);
				$doc = str_replace(".", "", $doc);
				$doc = str_replace("/", "", $doc);

				if(strlen($doc) == 11) $infCIOT->CPF = $doc;
				else $infCIOT->CNPJ = $doc;

				$mdfex->taginfCIOT($infCIOT);
			}
		}

		if($vale_pedagio){
			foreach($vale_pedagio as $v){
				$valePed = new \stdClass();
				$valePed->CNPJForn = $v['cnpj_contratante'];
				$doc = str_replace("-", "", $v['cnpj_fornecedor_pagador']);
				$doc = str_replace(".", "", $doc);
				$doc = str_replace("/", "", $doc);
				if(strlen($doc) == 11) $valePed->CPFPg = $doc;
				else $valePed->CNPJPg = $doc;

				$valePed->nCompra = $v['numero_compra'];
				$valePed->vValePed = $this->format($v['valor']);
				$mdfex->tagdisp($valePed);
			}
		}

		$contV = 1;
		$infContratante = new \stdClass();
		$doc = str_replace("-", "", $mdfe['cnpj_contratante']);
		$doc = str_replace(".", "", $doc);
		$doc = str_replace("/", "", $doc);
		$infContratante->CNPJ = $doc;
		$mdfex->taginfContratante($infContratante);
		/* Grupo veicTracao */
		$veicTracao = new \stdClass();
		$veicTracao->cInt = '0'.$contV;
		$placa = str_replace("-", "", $veiculoTracao['placa']);
		$veicTracao->placa = strtoupper($placa);
		$veicTracao->tara = $veiculoTracao['tara'];
		$veicTracao->capKG = $veiculoTracao['capacidade'];
		$veicTracao->tpRod = $veiculoTracao['tipo_rodado'];
		$veicTracao->tpCar = $veiculoTracao['tipo_carroceira'];
		$veicTracao->UF = $veiculoTracao['uf'];

		$condutor = new \stdClass();
		$condutor->xNome = $mdfe['condutor_nome']; // banco
		$condutor->CPF = $mdfe['condutor_cpf']; // banco
		$veicTracao->condutor = [$condutor];

		$prop = new \stdClass();

		$doc = str_replace("-", "", $veiculoTracao['cpf_cnpj_proprietario']);
		$doc = str_replace(".", "", $doc);
		$doc = str_replace("/", "", $doc);
		if(strlen($doc) == 11) $prop->CPF = $doc;
		else $prop->CNPJ = $doc;
		
		$prop->RNTRC = $veiculoTracao['rntrc'];
		$prop->xNome = $veiculoTracao['nome_proprietario'];
		$prop->IE = $veiculoTracao['ie_proprietario'];
		$prop->UF = $veiculoTracao['uf_proprietario'];
		$prop->tpProp = $veiculoTracao['tipo_proprietario'];
		$veicTracao->prop = $prop;

		$mdfex->tagveicTracao($veicTracao);

		/* fim veicTracao */

		/* Grupo veicReboque */
		foreach($veiculos as $v){
			$contV++;
			if($v['tipo_veiculo'] == 'r'){
				$veicReboque = new \stdClass();
				$veicReboque->cInt = '0'.$contV;
				$placa = str_replace("-", "", $v['placa']);

				$veicReboque->placa = strtoupper($placa);
				$veicReboque->tara = $v['tara'];
				$veicReboque->capKG = $v['capacidade'];
				$veicReboque->tpCar = $v['tipo_carroceira'];
				$veicReboque->UF = $v['uf'];

				$prop = new \stdClass();
				$doc = str_replace("-", "", $v['cpf_cnpj_proprietario']);
				$doc = str_replace(".", "", $doc);
				$doc = str_replace("/", "", $doc);
				if(strlen($doc) == 11) $prop->CPF = $doc;
				else $prop->CNPJ = $doc;

				$prop->RNTRC = $v['rntrc'];
				$prop->xNome = $v['nome_proprietario'];
				$prop->IE = $v['ie_proprietario'];
				$prop->UF = $v['uf_proprietario'];
				$prop->tpProp = $v['tipo_proprietario'];
				$veicReboque->prop = $prop;
				$mdfex->tagveicReboque($veicReboque);

			}
		}

		$lacRodo = new \stdClass();
		$lacRodo->nLacre = $mdfe['lacre_rodoviario'];//ver no banco
		$mdfex->taglacRodo($lacRodo);


		/*
		 * Grupo infDoc ( Documentos fiscais )
		 */
		$cont = 0;
		$contNFe = 0; 
		$contCTe = 0; 
		foreach($info_descarregamento as $key => $info) {

			$infMunDescarga = new \stdClass();
			$infMunDescarga->cMunDescarga = $info['cod_municipio_ibge'];
			$infMunDescarga->xMunDescarga = $info['nome_municipio'];
			$infMunDescarga->nItem = $key;
			$mdfex->taginfMunDescarga($infMunDescarga);

			if(isset($info['chaves_nfe'])){
				foreach($info['chaves_nfe'] as $c){
					$std = new \stdClass();
					$std->chNFe = $c['chave'];
					$std->SegCodBarra = '';
					$std->indReentrega = '1';
					$std->nItem = $cont;
					$contNFe++;
					$mdfex->taginfNFe($std);

				}
			}

			if(isset($info['chaves_cte'])){
				foreach($info['chaves_cte'] as $c){
					$std = new \stdClass();
					$std->chCTe = $c['chave'];
					$std->SegCodBarra = '';
					$std->indReentrega = '1';
					$std->nItem = $cont;
					$contCTe++;
					$mdfex->taginfCTe($std);
				}
			}
			

			/* Informações das Unidades de Transporte (Carreta/Reboque/Vagão) */
			$stdinfUnidTransp = new \stdClass();
			$stdinfUnidTransp->tpUnidTransp = $info['tipo_unidade_transporte'];
			$stdinfUnidTransp->idUnidTransp = $info['id_unidade_transporte'];

			/* Lacres das Unidades de Transporte */

			$lacres = [];

			if(isset($info['lacres_transporte'])){
				foreach($info['lacres_transporte'] as $l){
					array_push($lacres, $l['numero']);
				}
			}
			$stdlacUnidTransp = new \stdClass();
			$stdlacUnidTransp->nLacre = $lacres;

			$stdinfUnidTransp->lacUnidTransp = $stdlacUnidTransp;

			/* Informações das Unidades de Carga (Containeres/ULD/Outros) */
			$stdinfUnidCarga = new \stdClass();
			$stdinfUnidCarga->tpUnidCarga = '1';
			$stdinfUnidCarga->idUnidCarga = $info['id_unidade_carga'];


			/* Lacres das Unidades de Carga */
			$lacres = [];
			if(isset($info['lacres_unidade_carga'])){
				foreach($info['lacres_unidade_carga'] as $l){
					array_push($lacres, $l['numero']);
				}
			}
			$stdlacUnidCarga = new \stdClass();
			$stdlacUnidCarga->nLacre = $lacres;

			$stdinfUnidCarga->lacUnidCarga = $stdlacUnidCarga;
			$stdinfUnidCarga->qtdRat = $info['quantidade_rateio'];

			$stdinfUnidTransp->infUnidCarga = [$stdinfUnidCarga];
			$stdinfUnidTransp->qtdRat = $info['quantidade_rateio'];

			$std->infUnidTransp = [$stdinfUnidTransp];

			$cont++;

		}

		/* Grupo do Seguro */
		if($seguradora){
			$std = new \stdClass();
			$std->respSeg = '1';

			/* Informações da seguradora */
			$stdinfSeg = new \stdClass();
			$stdinfSeg->xSeg = $seguradora['nome'];
			$stdinfSeg->CNPJ = $seguradora['cnpj'];

			$std->infSeg = $stdinfSeg;
			$std->nApol = $seguradora['numero_apolice'];
			$std->nAver = [$seguradora['numero_averbacao']];
			$mdfex->tagseg($std);
			/* fim grupo Seguro */

		}

		/* grupo de totais */
		$std = new \stdClass();
		$std->vCarga = $this->format($mdfe['valor_carga']);
		$std->cUnid = '01';
		$std->qNFe = $contNFe;
		$std->qCTe = $contCTe;
		$std->qCarga = $mdfe['quantidade_carga'];
		$mdfex->tagtot($std);
		/* fim grupo de totais */
		$std = new \stdClass();
		$std->CNPJ = str_replace(" ", "", $emitente['cnpj']);
		$mdfex->tagautXML($std);

		try{
			$xml = $mdfex->getXML();
			header("Content-type: text/xml");

			return [
				'xml' => $xml,
				'numero' => $mdfeLast+1
			];
		}catch(\Exception $e){
			return [
				'erros_xml' => $mdfex->getErrors()
			];
		}


	}

	public function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml){
		return $this->tools->signMDFe($xml);
	}

	public function transmitir($signXml){
		try{
			$resp = $this->tools->sefazEnviaLote([$signXml], rand(1, 10000));

			$st = new Standardize();
			$std = $st->toStd($resp);


			sleep(5);

			$resp = $this->tools->sefazConsultaRecibo($std->infRec->nRec);
			$std = $st->toStd($resp);
			sleep(2);

			if(!isset($std->protMDFe)){
				return [
					'erro' => true, 
					'message' => 'Tente enviar novamente em minutos!', 
					'cStat' => '999',
					'std' => $std
				];
			}

			$chave = $std->protMDFe->infProt->chMDFe;
			$cStat = $std->protMDFe->infProt->cStat;

			if($cStat == '100'){
				header('Content-type: text/xml; charset=UTF-8');
				file_put_contents('public/xml_mdfe/' . $chave . '.xml', $signXml);
				return [
					'chave' => $chave, 
					'protocolo' => $std->protMDFe->infProt->nProt, 
					'cStat' => $cStat
				];
			}else{
				return [
					'erro' => true, 
					'message' => $std->protMDFe->infProt->xMotivo, 
					'cStat' => $cStat
				];
			}
			return $std->protMDFe->infProt->chMDFe;

		} catch(\Exception $e){
			return [
				'erro' => true, 
				'message' => $e->getMessage(),
				'cStat' => ''
			];
		}

	}	


	public function naoEncerrados(){
		try {
			
			$resp = $this->tools->sefazConsultaNaoEncerrados();

			$st = new Standardize();
			$std = $st->toArray($resp);

			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function encerrar($chave, $protocolo, $cUF, $cMun){
		try {
			$chave = $chave;
			$nProt = $protocolo;
			$dtEnc = date('Y-m-d'); // Opcional, caso nao seja preenchido pegara HOJE
			$resp = $this->tools->sefazEncerra($chave, $nProt, $cUF, $cMun, $dtEnc);

			$st = new Standardize();
			$std = $st->toStd($resp);

			return $std;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function consultar($chave){
		try {
			
			$chave = $chave;
			$resp = $this->tools->sefazConsultaChave($chave);

			$st = new Standardize();
			$std = $st->toStd($resp);
			$arr = $st->toArray($resp);


			return $arr;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function cancelar($chave, $protocolo, $justificativa){
		try {
			$xJust = $justificativa;
			$nProt = $protocolo;
			
			$chave = $chave;
			$resp = $this->tools->sefazCancela($chave, $xJust, $nProt);
			sleep(2);
			$st = new Standardize();
			$std = $st->toStd($resp);
			$arr = $st->toArray($resp);
			return $arr;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}


}
