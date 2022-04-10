<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DFeService;
use App\Models\Manifesto;
use App\Models\Certificado;
use App\Models\ItemManifesto;
use NFePHP\DA\NFe\Danfe;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

class DFeController extends Controller
{

	private $modelCertificado;
	private $modelManifesto;
	private $modelItemManifesto;

	public function __construct(
		Certificado $certificado,
		Manifesto $manifesto,
		ItemManifesto $item
	) {
		$this->modelCertificado = $certificado;
		$this->modelManifesto = $manifesto;
		$this->modelItemManifesto = $item;
	}
	
	public function all(){

		$docs = $this->modelManifesto->all();
		if (!empty($docs)) {
			return response()->json($docs, 200);
		} else {
			return response()->json("Nenhum documento encontrado", 404);
		}
	}

	public function filtro(Request $request){
		$cnpj = isset($request->cnpj) ? $request->cnpj : -1;
		$evento = isset($request->evento) ? $request->evento : -1;
		$docs = $this->modelManifesto->select('*');

		if($cnpj >= 0){
			$docs->where('cnpj', $cnpj);
		}
		if($evento >= 0){	
			$docs->where('tipo', $evento);
		}
		$docs->orderBy('id', 'desc');

		$docs = $docs->get();
		if (!empty($docs)) {
			return response()->json($docs, 200);
		} else {
			return response()->json("Nenhum documento encontrado", 404);
		}
	}

	public function novosDocumentos(Request $request){
		try{

			$cnpj = $request->cnpj;
			$razao_social = $request->razao_social;
			$uf = $request->uf;

			$certificado = $this->modelCertificado->where('cnpj', $cnpj)->first();

			if($certificado == null){
				return response()->json("Certificado não encontrado para este cnpj", 401);
			}

			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $razao_social,
				"siglaUF" => $uf,
				"cnpj" => $certificado->cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => "AAAAAAA", 
				"CSCid" => "000001"
			], $certificado);

			$manifesto = $this->modelManifesto
			->where('cnpj', $cnpj)
			->orderBy('nsu', 'desc')
			->first();

			if($manifesto == null) $nsu = 0;
			else $nsu = $manifesto->nsu;

			$docs = $dfe_service->novaConsulta($nsu, $certificado->cnpj, $razao_social, $uf);

			$novos = [];
			foreach($docs as $documento) {
				try{
					if($this->validaNaoInserido($documento['chave'], $certificado->cnpj)){
						if($documento['valor'] > 0 && $documento['nome']){
							$result = $this->modelManifesto->create($documento);

							array_push($novos, $documento);
						}
					}
				}catch(Exception $e){
					return response()->json($e->getMessage(), 403);
				}
			}

			return response()->json($novos, 200);

		}catch(Exception $e){
			return response()->json($e->getMessage(), 403);
		}

	}

	
	/*
	1 - Ciencia de operação
	2 - Confirmação
	3 - Desconhecimento
	4 - Operação não realizada
	*/
	public function manifestar(Request $request){
		$evento = $request->evento;
		$chave = $request->chave;
		$manifesto = $this->modelManifesto->where('chave', $chave)->first();

		$certificado = $this->modelCertificado->where('cnpj', $manifesto->cnpj)->first();

		if($certificado == null){
			return response()->json("Certificado não encontrado para este cnpj", 401);
		}

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $manifesto->razao_social,
			"siglaUF" => $manifesto->uf,
			"cnpj" => $certificado->cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => "AAAAAAA", 
			"CSCid" => "000001"
		], $certificado);


		$manifestaAnterior = $this->verificaAnterior($request->chave);

		if($evento == 1){
			$res = $dfe_service->manifesta($request->chave,	 
				$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
		}else if($evento == 2){
			$res = $dfe_service->confirmacao($request->chave,	 
				$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
		}else if($evento == 3){
			$res = $dfe_service->desconhecimento($request->chave,	 
				$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $request->justificativa);
		}else if($evento == 4){
			$res = $dfe_service->operacaoNaoRealizada($request->chave,	 
				$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $request->justificativa);
		}

		// echo $res['retEvento']['infEvento']['cStat'];
		if($res['retEvento']['infEvento']['cStat'] == '135'){ //sucesso
			// $manifesta = [
			// 	'chave' => $request->chave,
			// 	'nome' => $request->nome,
			// 	'documento' => $request->cnpj,
			// 	'valor' => $request->valor,
			// 	'num_prot' => $request->num_prot,
			// 	'data_emissao' => $request->data_emissao,
			// 	'sequencia_evento' => 1, 
			// 	'fatura_salva' => false,	 
			// 	'tipo' => $evento
			// ];

			$manifesto = $this->modelManifesto->where('chave', $request->chave)
			->first();
			$manifesto->tipo = $evento;
			$manifesto->save();

			// ManifestaDfe::create($manifesta);
			return response()->json('XML ' . $request->chave . ' manifestado!', 200);

		}else{

			// $manifesta = [
			// 	'chave' => $request->chave,
			// 	'nome' => $request->nome,
			// 	'documento' => $request->cnpj,
			// 	'valor' => $request->valor,
			// 	'num_prot' => $request->num_prot,
			// 	'data_emissao' => $request->data_emissao,
			// 	'sequencia_evento' => 1, 
			// 	'fatura_salva' => false,	
			// 	'tipo' => $evento 
			// ];

			$manifesto = $this->modelManifesto->where('chave', $request->chave)
			->first();
			$manifesto->tipo = $evento;
			$manifesto->save();

			// ManifestaDfe::create($manifesta);
			return response()->json('Já esta manifestado a chave ' . $request->chave, 200);
		}
		
	}

	private function verificaAnterior($chave){
		return Manifesto::where('chave', $chave)->first();
	}

	public function xml($chave){

		$manifesto = $this->modelManifesto->where('chave', $chave)->first();
		if($manifesto == null){
			return response()->json("Documento não encontrado!", 401);
		}
		$certificado = $this->modelCertificado->where('cnpj', $manifesto->cnpj)->first();
		if($manifesto->tipo != 1 && $manifesto->tipo != 2){
			return response()->json("Não é possível gerar DANFE", 401);
		}
		if($certificado == null){
			return response()->json("Certificado não encontrado para este cnpj", 401);
		}

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $manifesto->razao_social,
			"siglaUF" => $manifesto->uf,
			"cnpj" => $certificado->cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => "AAAAAAA", 
			"CSCid" => "000001"
		], $certificado);

		try{
			$response = $dfe_service->download($chave);
		// print_r($response);
			
			$stz = new Standardize($response);
			$std = $stz->toStd();
			if ($std->cStat != 138) {
				echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";  
				die();
			}    
			$zip = $std->loteDistDFeInt->docZip;
			$xml = gzdecode(base64_decode($zip));
			file_put_contents('public/xml_manifesto/' . $chave . '.xml', $xml);

			header('Content-Type: application/xml');
			header('Content-Disposition: attachment; filename=' . $chave . '.xml');
			header('Pragma: no-cache');
			readfile("public/xml_manifesto/$chave.xml");
		}catch(\Exception $e){
			echo "Erro de soap:<br>";
			echo $e->getMessage();
		}

	}

	public function imprimirDanfe($chave){

		$manifesto = $this->modelManifesto->where('chave', $chave)->first();
		if($manifesto == null){
			return response()->json("Documento não encontrado!", 401);
		}
		$certificado = $this->modelCertificado->where('cnpj', $manifesto->cnpj)->first();

		if($manifesto->tipo != 1 && $manifesto->tipo != 2){
			return response()->json("Não é possível gerar DANFE", 401);
		}
		if($certificado == null){
			return response()->json("Certificado não encontrado para este cnpj", 401);
		}

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $manifesto->razao_social,
			"siglaUF" => $manifesto->uf,
			"cnpj" => $certificado->cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => "AAAAAAA", 
			"CSCid" => "000001"
		], $certificado);

		try{
			$response = $dfe_service->download($chave);
		// print_r($response);
			
			$stz = new Standardize($response);
			$std = $stz->toStd();
			if ($std->cStat != 138) {
				echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";  
				die();
			}    
			$zip = $std->loteDistDFeInt->docZip;
			$xml = gzdecode(base64_decode($zip));

			$danfe = new Danfe($xml);
			$id = $danfe->monta($logo);
			$pdf = $danfe->render();

			return response($pdf)
			->header('Content-Type', 'application/pdf');
			

		}catch(\Exception $e){
			echo "Erro de soap:<br>";
			echo $e->getMessage();
		}

	}

	
	public function imprimirDanfe2($chave){
		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], 55);

		$response = $dfe_service->download($chave);
		// print_r($response);
		try {
			$stz = new Standardize($response);
			$std = $stz->toStd();
			if ($std->cStat != 138) {
				echo "Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!";  
				die;
			}    
			$zip = $std->loteDistDFeInt->docZip;
			$xml = gzdecode(base64_decode($zip));
			
			$public = getenv('SERVIDOR_WEB') ? 'public/' : '';

			file_put_contents($public.'xml_dfe/'.$chave.'.xml',$xml);

			
			$danfe = new Danfe($xml);
			$id = $danfe->monta();
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			echo $pdf;
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
		

	}

	

	private function getItensDaNFe($xml){
		$itens = [];
		foreach($xml->NFe->infNFe->det as $item) {

			$produto = Produto::verificaCadastrado($item->prod->cEAN,
				$item->prod->xProd, $item->prod->cProd);

			$produtoNovo = !$produto ? true : false;

			$tp = null;
			if($produto != null){
				$tp = ItemDfe::
				where('produto_id', $produto->id)
				->where('numero_nfe', $xml->NFe->infNFe->ide->nNF)
				->first();

			}

			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $item->prod->xProd,
				'NCM' => $item->prod->NCM,
				'CFOP' => $item->prod->CFOP,
				'uCom' => $item->prod->uCom,
				'vUnCom' => $item->prod->vUnCom,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN,
				'produtoNovo' => $produtoNovo,
				'produto_id' => $produtoNovo ? null : $produto->id,
				'produtoSetadoEstoque' => $tp != null ? true : false,
				'produtoId' => $produtoNovo ? '0' : $produto->id,
				'conversao_unitaria' => $produtoNovo ? '' : $produto->conversao_unitaria
			];
			array_push($itens, $item);
		}

		return $itens;
	}



	private function formataCnpj($cnpj){
		$temp = substr($cnpj, 0, 2);
		$temp .= ".".substr($cnpj, 2, 3);
		$temp .= ".".substr($cnpj, 5, 3);
		$temp .= "/".substr($cnpj, 8, 4);
		$temp .= "-".substr($cnpj, 12, 2);
		return $temp;
	}

	private function formataCep($cep){
		$temp = substr($cep, 0, 5);
		$temp .= "-".substr($cep, 5, 3);
		return $temp;
	}

	private function formataTelefone($fone){
		$temp = substr($fone, 0, 2);
		$temp .= " ".substr($fone, 2, 4);
		$temp .= "-".substr($fone, 4, 4);
		return $temp;
	}

	

	

	private function validaNaoInserido($chave, $cnpj){
		$m = Manifesto::
		where('chave', $chave)
		->where('cnpj', $cnpj)
		->first();
		if($m == null) return true;
		else return false;
	}

	public function downloadXml($chave){
		$dfe = Manifesto::where('chave', $chave)->first();
		$chave = $dfe->chave; 
		$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists($public.'xml_dfe/'.$chave.'.xml'))
			return response()->download($public.'xml_dfe/'.$chave.'.xml');
		else echo "Erro ao baixar XML, arquivo não encontrado!";
	}
}
