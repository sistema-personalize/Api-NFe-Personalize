<?php

namespace App\Services;
use NFePHP\CTe\Make;
use NFePHP\CTe\Tools;
use NFePHP\CTe\Complements;
use NFePHP\CTe\Common\Standardize;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use App\Models\Cte;
use App\Models\Certificado;

error_reporting(E_ALL);
ini_set('display_errors', 'On');
class CTeService{

	private $config; 
	private $tools;

	public function __construct($config, $certificado){
		$this->config = $config;
		if($certificadoDigital == null){
			$certificadoDigital = file_get_contents("public/certificados/".$certificado->path_arquivo);
		}
		$certificadoDigital = $certificado->arquivo;
		$senha = $certificado->senha;

		$this->tools = new Tools(json_encode($config), Certificate::readPfx($certificadoDigital, $senha));
		$this->tools->model('57');
		
	}

	public function gerar(
		$documento,
		$emitente,
		$enderecoTomador,
		$remetente,
		$destinatario,
		$chaves_nfe,
		$doc_outros,
		$componentes,
		$medidas
	){

		$cte = new Make();
		$dhEmi = date("Y-m-d\TH:i:sP");
		$numeroCTE = $documento['numero'];

		$cnpj = $emitente['cnpj'];
		$chave = $this->montaChave(
			Cte::getCodUF($emitente['uf']), date('y', strtotime($dhEmi)), date('m', strtotime($dhEmi)), $cnpj, $this->tools->model(), '1', $numeroCTE, '1', '10'
		);
		$infCte = new \stdClass();
		$infCte->Id = "";
		$infCte->versao = "3.00";
		$cte->taginfCTe($infCte);


		$cDV = substr($chave, -1);      
		$ide = new \stdClass();

		$ide->cUF = Cte::getCodUF($emitente['uf']); 
		$ide->cCT = rand(11111111, 99999999); 
		$ide->CFOP = $documento['cfop'];
		$ide->natOp = $documento['natureza_operacao'];
		$ide->mod = '57'; 
		$ide->serie = '1'; 
		$nCte = $ide->nCT = $numeroCTE; 
		$ide->dhEmi = $dhEmi; 
		$ide->tpImp = '1'; 
		$ide->tpEmis = '1'; 
		$ide->cDV = $cDV; 
		$ide->tpAmb = $documento['ambiente']; 
		$ide->tpCTe = '0'; 

		// 0- CT-e Normal; 1 - CT-e de Complemento de Valores;
// 2 -CT-e de Anulação; 3 - CT-e Substituto

		$ide->procEmi = '0'; 
		$ide->verProc = '3.0'; 
		$ide->indGlobalizado = '';

		$ide->cMunEnv = $documento['codigo_mun_envio']; /*////////////////////////////*/

		$ide->xMunEnv = $documento['nome_municipio_envio']; /*////////////////////////////*/
		$ide->UFEnv = $documento['uf_municipio_envio']; /*////////////////////////////*/
		$ide->modal = $documento['modal']; /*////////////////////////////*/
		$ide->tpServ = '0'; 

		$ide->cMunIni = $documento['codigo_municipio_inicio']; /*////////////////////////////*/
		$ide->xMunIni = $documento['nome_municipio_inicio']; /*////////////////////////////*/
		$ide->UFIni = $documento['uf_municipio_inicio'];  /*////////////////////////////*/
		$ide->cMunFim = $documento['codigo_municipio_fim']; /*////////////////////////////*/
		$ide->xMunFim = $documento['nome_municipio_fim']; /*////////////////////////////*/
		$ide->UFFim = $documento['uf_municipio_fim']; /*////////////////////////////*/
		$ide->retira = $documento['retira']; /*////////////////////////////*/
		$ide->xDetRetira = $documento['detalhes_retira']; /*////////////////////////////*/

		if($documento['tomador'] == 0){ /*////////////////////////////*/
			
			if($remetente['ie'] == 'ISENTO'){
				$ide->indIEToma = '2';
			}else{
				$ide->indIEToma = '1';
			}
			
		}else if($documento['tomador'] == 3){
			if($destinatario['ie_rg'] == 'ISENTO'){
				$ide->indIEToma = '2';
			}else{
				$ide->indIEToma = '1';
			}
		}

		// $ide->indIEToma = $cteEmit->destinatario;
		$ide->dhCont = ''; 
		$ide->xJust = '';

		$cte->tagide($ide);
		// Indica o "papel" do tomador: 0-Remetente; 1-Expedidor; 2-Recebedor; 3-Destinatário
		$toma3 = new \stdClass();
		$toma3->toma = $documento['tomador'];
		$cte->tagtoma3($toma3);

		$enderToma = new \stdClass();
		$enderToma->xLgr = $enderecoTomador['logradouro'];/*////////////////////////////*/
		$enderToma->nro = $enderecoTomador['numero']; /*////////////////////////////*/
		$enderToma->xCpl = $enderecoTomador['complemento']; /*////////////////////////////*/
		$enderToma->xBairro = $enderecoTomador['bairro']; /*////////////////////////////*/
		$enderToma->cMun = $enderecoTomador['codigo_municipio_ibge']; /*////////////////////////////*/
		$enderToma->xMun = $enderecoTomador['nome_municipio']; /*////////////////////////////*/
		$enderToma->CEP = $enderecoTomador['cep']; /*////////////////////////////*/
		$enderToma->UF = $enderecoTomador['uf']; /*////////////////////////////*/
		$enderToma->cPais = '1058'; /*////////////////////////////*/
		$enderToma->xPais = 'Brasil';                   /*////////////////////////////*/
		$cte->tagenderToma($enderToma);   

		$emit = new \stdClass();
		
		$emit->CNPJ = $cnpj; 

		$ie = str_replace(".", "", $emitente['ie']);
		$ie = str_replace("/", "", $ie);
		$ie = str_replace("-", "", $ie);
		$emit->IE = $ie; 
		$emit->IEST = "";
		$emit->xNome = $emitente['razao_social']; /*////////////////////////////*/
		$emit->xFant = $emitente['nome_fantasia']; /*////////////////////////////*/
		$cte->tagemit($emit); 


		$enderEmit = new \stdClass();
		$enderEmit->xLgr = $emitente['logradouro']; /*////////////////////////////*/
		$enderEmit->nro = $emitente['numero']; /*////////////////////////////*/
		$enderEmit->xCpl = $emitente['complemento'];/*////////////////////////////*/
		$enderEmit->xBairro = $emitente['bairro']; /*////////////////////////////*/
		$enderEmit->cMun = $emitente['cod_municipio_ibge'];/*////////////////////////////*/
		$enderEmit->xMun = $emitente['nome_municipio']; /*////////////////////////////*/

		$cep = str_replace("-", "", $emitente['cep']);
		$cep = str_replace(".", "", $cep);
		$enderEmit->CEP = $cep; 
		$enderEmit->UF = $emitente['uf']; /*////////////////////////////*/

		$fone = str_replace(" ", "", $emitente['telefone']);
		$fone = str_replace("-", "", $fone);
		$enderEmit->fone = $fone; 
		$cte->tagenderEmit($enderEmit);/*////////////////////////////*/

		$rem = new \stdClass();

		$cnpjRemente = str_replace(".", "", $remetente['cnpj']);
		$cnpjRemente = str_replace("/", "", $cnpjRemente);
		$cnpjRemente = str_replace("-", "", $cnpjRemente);
		if(strlen($cnpjRemente) == 14){
			$rem->CNPJ = $cnpjRemente; 

			$ieRemetente = str_replace(".", "", $remetente['ie']);
			$ieRemetente = str_replace("/", "", $ieRemetente);
			$ieRemetente = str_replace("-", "", $ieRemetente);
			$rem->IE = $ieRemetente;
		}
		else{
			$rem->CPF = $cnpjRemente; 
		}

		$rem->xNome = $remetente['razao_social'];
		$rem->xFant = $remetente['nome_fantasia']; 
		$rem->fone = $remetente['fone']; 
		$rem->email = $remetente['email']; 
		$cte->tagrem($rem);

		$enderReme = new \stdClass();
		$enderReme->xLgr = $remetente['logradouro']; 
		$enderReme->nro = $remetente['numero']; 
		$enderReme->xCpl = $remetente['complemento']; 
		$enderReme->xBairro = $remetente['bairro']; 
		$enderReme->cMun = $remetente['codigo_municipio_ibge']; 
		$enderReme->xMun = $remetente['nome_municipio']; 
		$enderReme->CEP = $remetente['cep']; 
		$enderReme->UF = $remetente['uf']; 
		$enderReme->cPais = '1058'; 
		$enderReme->xPais = 'Brasil'; 
		$cte->tagenderReme($enderReme);

		$dest = new \stdClass();
		$cnpjDestinatario = str_replace(".", "", $destinatario['cpf_cnpj']);
		$cnpjDestinatario = str_replace("/", "", $cnpjDestinatario);
		$cnpjDestinatario = str_replace("-", "", $cnpjDestinatario);
		if(strlen($cnpjDestinatario) == 14){
			$dest->CNPJ = $cnpjDestinatario; 

			$ieDestinatario = str_replace(".", "", $destinatario['ie_rg']);
			$ieDestinatario = str_replace("/", "", $ieDestinatario);
			$ieDestinatario = str_replace("-", "", $ieDestinatario);
			$dest->IE = $ieDestinatario;
		}
		else{
			$dest->CPF = $cnpjDestinatario; 
		}
		
		$dest->xNome = $destinatario['razao_social'];
		$dest->fone = $destinatario['fone']; 
		$dest->ISUF = ''; 
		$dest->email = $destinatario['email']; 
		$cte->tagdest($dest);

		$enderDest = new \stdClass();
		$enderDest->xLgr = $destinatario['logradouro']; 
		$enderDest->nro = $destinatario['numero']; 
		$enderDest->xCpl = $destinatario['complemento']; 
		$enderDest->xBairro = $destinatario['bairro']; 
		$enderDest->cMun = $destinatario['codigo_municipio_ibge']; 
		$enderDest->xMun = $destinatario['nome_municipio']; 

		$enderDest->CEP = $destinatario['cep']; 
		$enderDest->UF = $destinatario['uf']; 
		$enderDest->cPais = '1058'; 
		$enderDest->xPais = 'Brasil'; 
		$cte->tagenderDest($enderDest);

		$vPrest = new \stdClass();
		$vPrest->vTPrest = $this->format($documento['valor_transporte']); 
		$vPrest->vRec = $this->format($documento['valor_receber']);      
		$cte->tagvPrest($vPrest);

		foreach($componentes as $c){
			$comp = new \stdClass();
			$comp->xNome = $c['nome']; 
			$comp->vComp = $this->format($c['valor']);  
			$cte->tagComp($comp);
		}


		$icms = new \stdClass();
		$icms->cst = $documento['cst'];
		$icms->pRedBC = ''; 
		$icms->vBC = 0.00; 
		$icms->pICMS = $documento['perc_icms']; 
		$icms->vICMS = 0.00; 
		$icms->vBCSTRet = ''; 
		$icms->vICMSSTRet = ''; 
		$icms->pICMSSTRet = ''; 
		$icms->vCred = ''; 
		$icms->vTotTrib = 0.00; 
		$icms->outraUF = false;    
		$icms->vICMSUFIni = 0;  
		$icms->vICMSUFFim = 0;
		$icms->infAdFisco = '';
		$cte->tagicms($icms);
		$cte->taginfCTeNorm();              // Grupo de informações do CT-e Normal e Substituto

		$infCarga = new \stdClass();
		$infCarga->vCarga = $this->format($documento['valor_carga']);
		$infCarga->proPred = $documento['produto_predominante']; 
		$infCarga->xOutCat = 0.00; 

		// $infCarga->vCargaAverb = 1.99;
		$cte->taginfCarga($infCarga);

		foreach($medidas as $m){
			$infQ = new \stdClass();
			$infQ->cUnid = $m['cod_unidade']; 
// Código da Unidade de Medida: ( 00-M3; 01-KG; 02-TON; 03-UNIDADE; 04-LITROS; 05-MMBTU
			$infQ->tpMed = $m['tipo_medida']; 
// Tipo de Medida
// ( PESO BRUTO; PESO DECLARADO; PESO CUBADO; PESO AFORADO; PESO AFERIDO; LITRAGEM; CAIXAS e etc)
			$infQ->qCarga = $m['quantidade_carga'];  
// Quantidade (15 posições; sendo 11 inteiras e 4 decimais.)
			$cte->taginfQ($infQ);
		}

		if(isset($chaves_nfe) && sizeof($chaves_nfe) > 0){
			foreach($chaves_nfe as $c){
				$infNFe = new \stdClass();
				$infNFe->chave = $c['chave']; 
				$infNFe->PIN = ''; 
				$infNFe->dPrev = $documento['data_prevista_entrega'];                                       
				$cte->taginfNFe($infNFe);
			}
		}else{
			foreach($doc_outros as $doc){
				$infOut = new \stdClass();
				$infOut->tpDoc = $doc['tipo'];     
				$infOut->descOutros = $doc['descricao'];     
				$infOut->nDoc = $doc['numero'];     
				$infOut->dEmi = $doc['data_emissao'];     
				$infOut->vDocFisc = $this->format($doc['valor']);     
				$infOut->dPrev = $documento['data_prevista_entrega'];     
				$cte->taginfOutros($infOut);
			}
		}

		$infModal = new \stdClass();
		$infModal->versaoModal = '3.00';
		$cte->taginfModal($infModal);

		$rodo = new \stdClass();
		$rodo->RNTRC = $documento['rntrc'];
		$cte->tagrodo($rodo);

		$aereo = new \stdClass();
		$aereo->nMinu = '123'; 
		$aereo->nOCA = '';
 // Número Operacional do Conhecimento Aéreo
		$aereo->dPrevAereo = date('Y-m-d');
		$aereo->natCarga_xDime = ''; 
		$aereo->natCarga_cInfManu = [  ]; 
		$aereo->tarifa_CL = 'G';
		$aereo->tarifa_cTar = ''; 
		$aereo->tarifa_vTar = 100.00; 
		// $cte->tagaereo($aereo);

// 		$autXML = new \stdClass();
// 		// $cnpj = str_replace(".", "", $config->cnpj);
// 		// $cnpj = str_replace("/", "", $cnpj);
// 		// $cnpj = str_replace("-", "", $cnpj);
// 		// $cnpj = str_replace(" ", "", $cnpj);
// 		$autXML->CNPJ = '08543628000145'; 
// // CPF ou CNPJ dos autorizados para download do XML
// 		$cte->tagautXML($autXML);


		try{
			$cte->montaCTe();
			$chave = $cte->chCTe;
			$xml = $cte->getXML();
			$arr = [
				'chave' => $chave,
				'xml' => $xml,
				'nCte' => $nCte
			];
			return $arr;
		}catch(\Exception $e){
			return [
				'erros_xml' => $cte->getErrors()
			];
		}
	}

	public function sign($xml){
		return $this->tools->signCTe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = substr(str_replace(',', '', number_format(microtime(true) * 1000000, 0)), 0, 15);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);
			sleep(1);
			$st = new Standardize($resp);
			sleep(2);

			$arr = $st->toArray();
			$std = $st->toStd();

			if ($std->cStat != 103) {
				// erro
				return "[$std->cStat] - $std->xMotivo";
			}

			$recibo = $std->infRec->nRec; 
			$protocolo = $this->tools->sefazConsultaRecibo($recibo);
			sleep(3);
			// return $protocolo;
			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				header('Content-type: text/xml; charset=UTF-8');
				file_put_contents('public/xml_cte/' . $chave . '.xml', $xml);

				return $recibo;
				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return $st->toArray($protocolo);
			}

		} catch(\Exception $e){
			return "Erro: ".$e->getMessage() ;
		}

	}	


	private function format($number, $dec = 2){
		return number_format((float) $number, $dec, ".", "");
	}

	private function montaChave($cUF, $ano, $mes, $cnpj, $mod, $serie, 
		$numero, $tpEmis, $codigo = ''){
		if ($codigo == '') {
			$codigo = $numero;
		}
		$forma = "%02d%02d%02d%s%02d%03d%09d%01d%08d";
		$chave = sprintf(
			$forma, $cUF, $ano, $mes, $cnpj, $mod, $serie, $numero, $tpEmis, $codigo
		);
		return $chave . $this->calculaDV($chave);
	}

	private function calculaDV($chave43){
		$multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
		$iCount = 42;
		$somaPonderada = 0;
		while ($iCount >= 0) {
			for ($mCount = 0; $mCount < count($multiplicadores) && $iCount >= 0; $mCount++) {
				$num = (int) substr($chave43, $iCount, 1);
				$peso = (int) $multiplicadores[$mCount];
				$somaPonderada += $num * $peso;
				$iCount--;
			}
		}
		$resto = $somaPonderada % 11;
		if ($resto == '0' || $resto == '1') {
			$cDV = 0;
		} else {
			$cDV = 11 - $resto;
		}
		return (string) $cDV;
	}


	public function cancelar($cte, $justificativa){

		try {
			
			$chave = $cte->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			$js = $stdCl->toJson();
			sleep(2);
			$xJust = $justificativa;

			$nProt = $arr['protCTe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $xJust, $nProt);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			// return $json;
			$cStat = $std->infEvento->cStat;

			if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
				$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
				// header('Content-type: text/xml; charset=UTF-8');
				file_put_contents('public/xml_cte_cancelada/' . $chave . '.xml', $xml);

				return $arr;
			}else{
				return $arr;
			}

		} catch (\Exception $e) {
			return $e->getMessage();
    //TRATAR
		}
	}

	public function consultar($cte){
		try {

			$chave = $cte->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			// $arr = json_decode($json);
			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function inutilizar($nInicio, $nFinal, $justificativa){
		try{

			$nSerie = '1';
			$nIni = $nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$tpAmb = 2;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust, $tpAmb);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function cartaCorrecao($cte, $grupo, $campo, $valor){
		try {

			$chave = $cte->chave;

			$nSeqEvento = $cte->sequencia_cce+1;
			$infCorrecao[] = [
				'grupoAlterado' => $grupo,
				'campoAlterado' => $campo,
				'valorAlterado' => $valor,
				'nroItemAlterado' => '01'
			];
			$response = $this->tools->sefazCCe($chave, $infCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			$cStat = $std->infEvento->cStat;
			$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
			if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
				$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
				file_put_contents('public/xml_cte_correcao/' . $chave . '.xml', $xml);
				return $arr;
			}else{
				 //houve alguma falha no evento 
				return $arr;
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}