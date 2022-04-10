<?php

namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFCeService
{
    private $config;
    private $tools;

    public function __construct($config, $certificado)
    {

        $this->config = $config;
        //diretorio do certificado
        $certificadoDigital = $certificado->arquivo;
        if($certificadoDigital == null){
            $certificadoDigital = file_get_contents("public/certificados/".$certificado->path_arquivo);
        }
        $senha = $certificado->senha;

        $this->tools = new Tools(json_encode($config), Certificate::readPfx($certificadoDigital, $senha)); // senha do certificado
        $this->tools->model(65);
    }

    public function gerar(
        $documento,
        $emitente,
        $destinatario,
        $itens,
        $frete,
        $respTecnico,
        $pagamento,
        $fatura,
        $duplicatas
    ) {

        $nfe = new Make();
        $stdInNFe = new \stdClass();
        $stdInNFe->versao = '4.00';
        $stdInNFe->Id = null;
        $stdInNFe->pk_nItem = '';

        $infNFe = $nfe->taginfNFe($stdInNFe);


        $stdIde = new \stdClass();
        $stdIde->cUF = $emitente['codigo_uf']; // codigo uf emitente
        $stdIde->cNF = rand(11111, 99999);
        // $stdIde->natOp = $venda->natureza->natureza;
        $stdIde->natOp = $documento['natureza_operacao'];

        // $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

        $stdIde->mod = 65;
        $stdIde->serie = 1;
        $stdIde->nNF = $documento['numero_nfce']; // numero sequencial da nfe
        $stdIde->dhEmi = date("Y-m-d\TH:i:sP");
        $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
        $stdIde->tpNF = 1;
        $stdIde->idDest = $documento['operacao_interestadual'];
        $stdIde->cMunFG = $emitente['cod_municipio_ibge'];
        $stdIde->tpImp = 4;
        $stdIde->tpEmis = 1;
        $stdIde->cDV = 0;
        $stdIde->tpAmb = (int) $documento['ambiente'];
        $stdIde->finNFe = 1;
        $stdIde->indFinal = $documento['consumidor_final'];
        $stdIde->indPres = 1;
        $stdIde->procEmi = '0';
        $stdIde->verProc = '2.0';
        // $stdIde->dhCont = null;
        // $stdIde->xJust = null;

        //
        $tagide = $nfe->tagide($stdIde);

        $stdEmit = new \stdClass();
        $stdEmit->xNome = $emitente['razao_social'];
        $stdEmit->xFant = $emitente['nome_fantasia'];

        $stdEmit->CRT = $emitente['crt'];
        $stdEmit->IE = $emitente['ie'];

        $stdEmit->CNPJ = $emitente['cnpj'];
        $emit = $nfe->tagemit($stdEmit);

        // ENDERECO EMITENTE
        $stdEnderEmit = new \stdClass();
        $stdEnderEmit->xLgr = $emitente['logradouro'];
        $stdEnderEmit->nro = $emitente['numero'];
        $stdEnderEmit->xCpl = $emitente['complemento'];
        $stdEnderEmit->xBairro = $emitente['bairro'];
        $stdEnderEmit->cMun = $emitente['cod_municipio_ibge'];
        $stdEnderEmit->xMun = $emitente['nome_municipio'];
        $stdEnderEmit->UF = $emitente['uf'];
        $stdEnderEmit->CEP = $emitente['cep'];
        $stdEnderEmit->cPais = $emitente['cod_pais'];
        $stdEnderEmit->xPais = $emitente['nome_pais'];

        $enderEmit = $nfe->tagenderEmit($stdEnderEmit);

        // DESTINATARIO

        if ($destinatario != null) {
            $stdDest = new \stdClass();
            $stdDest->xNome = $destinatario['nome'];

            if ($destinatario['contribuinte'] == 1) {
                if ($destinatario['ie_rg'] == 'ISENTO') {
                    $stdDest->indIEDest = "2";
                } else {
                    $stdDest->indIEDest = "1";
                }
            } else {
                $stdDest->indIEDest = "9";
            }

            if ($destinatario['tipo'] == 'j') {
                $stdDest->CNPJ = $destinatario['cpf_cnpj'];
                $stdDest->IE = $destinatario['ie_rg'];
            } else {
                $stdDest->CPF = $destinatario['cpf_cnpj'];
            }

            $dest = $nfe->tagdest($stdDest);

            $stdEnderDest = new \stdClass();
            $stdEnderDest->xLgr = $destinatario['logradouro'];
            $stdEnderDest->nro = $destinatario['numero'];
            $stdEnderDest->xCpl = $destinatario['complemento'];
            $stdEnderDest->xBairro = $destinatario['bairro'];
            $stdEnderDest->cMun = $destinatario['cod_municipio_ibge'];
            $stdEnderDest->xMun = $destinatario['nome_municipio'];
            $stdEnderDest->UF = $destinatario['uf'];
            $stdEnderDest->CEP = $destinatario['cep'];
            $stdEnderDest->cPais = $destinatario['cod_pais'];
            $stdEnderDest->xPais = $destinatario['nome_pais'];

            $enderDest = $nfe->tagenderDest($stdEnderDest);
        }

        $somaProdutos = 0;
        $somaICMS = 0;

        //PRODUTOS
        $itemCont = 0;

        $totalItens = count($itens);
        $somaFrete = 0;

        foreach ($itens as $i) {
            $itemCont++;

            $stdProd = new \stdClass();
            $stdProd->item = $itemCont;
            $stdProd->cEAN = $i['cod_barras'];
            $stdProd->cEANTrib = $i['cod_barras'];
            $stdProd->cProd = $i['codigo_produto'];
            $stdProd->xProd = $i['nome_produto'];
            $stdProd->NCM = $i['ncm'];
            $stdProd->CFOP = $i['cfop'];
            $stdProd->uCom = $i['unidade'];
            $stdProd->qCom = $i['quantidade'];
            $stdProd->vUnCom = $this->format($i['valor_unitario']);
            $stdProd->vProd = $this->format(($i['quantidade'] * $i['valor_unitario']));
            $stdProd->uTrib = $i['unidade'];
            $stdProd->qTrib = $i['quantidade'];
            $stdProd->vUnTrib = $this->format($i['valor_unitario']);
            $stdProd->indTot = $i['compoe_valor_total'];
            $somaProdutos += ($i['quantidade'] * $i['valor_unitario']);


            $prod = $nfe->tagprod($stdProd);

            //TAG IMPOSTO

            $stdImposto = new \stdClass();
            $stdImposto->item = $itemCont;

            $imposto = $nfe->tagimposto($stdImposto);



            if ($emitente['crt'] == 1) {
                $stdICMS = new \stdClass();

                $stdICMS->item = $itemCont; 
                $stdICMS->orig = 0;
                $stdICMS->CSOSN = $i['cst_csosn'];

                if(isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
                if(isset($i['pST'])) $stdICMS->pST = $i['pST'];
                if(isset($i['vICMSSTRet'])) $stdICMS->vICMSSTRet = $i['vICMSSTRet'];
                if(isset($i['vICMSSubstituto'])) $stdICMS->vICMSSubstituto = $i['vICMSSubstituto'];
                

                $stdICMS->pCredSN = $this->format($i['perc_icms']);
                $stdICMS->vCredICMSSN = $this->format($i['perc_icms']);
                $ICMS = $nfe->tagICMSSN($stdICMS);

                $somaICMS = 0;
            } else if ($emitente['crt'] == 3) {

                $stdICMS = new \stdClass();
                $stdICMS->item = $itemCont; 
                $stdICMS->orig = 0;
                $stdICMS->CST = $i['cst_csosn'];
                $stdICMS->modBC = 0;
                $stdICMS->vBC = $this->format($i['valor_unitario'] * $i['quantidade']);
                $stdICMS->pICMS = $this->format($i['perc_icms']);
                $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

                if(isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
                if(isset($i['pST'])) $stdICMS->vBCSTRet = $i['pST'];
                if(isset($i['vICMSSTRet'])) $stdICMS->vBCSTRet = $i['vICMSSTRet'];
                if(isset($i['vICMSSubstituto'])) $stdICMS->vBCSTRet = $i['vICMSSubstituto'];

                $somaICMS += (($i['valor_unitario'] * $i['quantidade']) 
                    * ($stdICMS->pICMS/100));
                    $ICMS = $nfe->tagICMS($stdICMS);
                }



                $stdPIS = new \stdClass();
                $stdPIS->item = $itemCont;
                $stdPIS->CST = $i['cst_pis'];
                $stdPIS->vBC = $this->format($i['perc_pis']) > 0 ? $stdProd->vProd : 0.00;
                $stdPIS->pPIS = $this->format($i['perc_pis']);
                $stdPIS->vPIS = $this->format($stdProd->vProd * ($i['perc_pis'] / 100));

                $PIS = $nfe->tagPIS($stdPIS);

            //COFINS
                $stdCOFINS = new \stdClass();
                $stdCOFINS->item = $itemCont;
                $stdCOFINS->CST = $i['cst_cofins'];
                $stdCOFINS->vBC = $this->format($i['cst_cofins']) > 0 ? $stdProd->vProd : 0.00;
                $stdCOFINS->pCOFINS = $this->format($i['perc_cofins']);
                $stdCOFINS->vCOFINS = $this->format($stdProd->vProd * ($i['perc_cofins'] / 100));
                $COFINS = $nfe->tagCOFINS($stdCOFINS);

            }

            $stdICMSTot = new \stdClass();
            $stdICMSTot->vBC = 0.00;
            $stdICMSTot->vICMS = $this->format($somaICMS);
            $stdICMSTot->vICMSDeson = 0.00;
            $stdICMSTot->vBCST = 0.00;
            $stdICMSTot->vST = 0.00;
            $stdICMSTot->vProd = $this->format($somaProdutos);

        // $stdICMSTot->vFrete = 0.00;

            $stdICMSTot->vSeg = 0.00;
            $stdICMSTot->vDesc = 0.00;
            $stdICMSTot->vII = 0.00;
            $stdICMSTot->vIPI = 0.00;
            $stdICMSTot->vPIS = 0.00;
            $stdICMSTot->vCOFINS = 0.00;
            $stdICMSTot->vOutro = 0.00;

        // if($venda->frete){
        // 	$stdICMSTot->vNF = 
        // 	$this->format(($somaProdutos+$venda->frete->valor)-$venda->desconto);
        // } 
            $stdICMSTot->vNF = $this->format($somaProdutos);

            $stdICMSTot->vTotTrib = 0.00;
            $ICMSTot = $nfe->tagICMSTot($stdICMSTot);


            $stdTransp = new \stdClass();
            $stdTransp->modFrete = 9;

            $transp = $nfe->tagtransp($stdTransp);

            $stdPag = new \stdClass();
            $stdPag->vTroco = $this->format($pagamento['troco']);

            $pag = $nfe->tagpag($stdPag);

            $stdResp = new \stdClass();
            $stdResp->CNPJ = $respTecnico['cnpj'];
            $stdResp->xContato = $respTecnico['contato'];
            $stdResp->email = $respTecnico['email'];
            $stdResp->fone = $respTecnico['telefone'];

            $nfe->taginfRespTec($stdResp);

            $stdDetPag = new \stdClass();
            $stdDetPag->indPag = 0;

            $stdDetPag->tPag = $pagamento['tipo']; 
            $stdDetPag->vPag = $this->format($pagamento['valor_recebido']);

            if($pagamento['tipo'] == '03' || $pagamento['tipo'] == '04'){
               $stdDetPag->CNPJ = '12345678901234';
               $stdDetPag->tBand = '01';
               $stdDetPag->cAut = '3333333';
               $stdDetPag->tpIntegra = 1;
           }

           $stdPag = new \stdClass();
           $pag = $nfe->tagpag($stdPag);

           $stdDetPag = new \stdClass();


           $stdDetPag->tPag = $pagamento['tipo'];
           $stdDetPag->vPag = $this->format($somaProdutos);
           $stdDetPag->indPag = $pagamento['indicacao_pagamento'];

           $detPag = $nfe->tagdetPag($stdDetPag);


           $stdInfoAdic = new \stdClass();
           $stdInfoAdic->infCpl = $documento['info_complementar'];

           $infoAdic = $nfe->taginfAdic($stdInfoAdic);



           if ($nfe->montaNFe()) {
            $arr = [
                'chave' => $nfe->getChave(),
                'xml' => $nfe->getXML(),
                'nNf' => $stdIde->nNF
            ];
            return $arr;
        } else {
            throw new \Exception("Erro ao gerar NFCe");
        }
    }

    public function format($number, $dec = 2)
    {
        return number_format((float) $number, $dec, ".", "");
    }


    public function consultaChave($chave)
    {
        $response = $this->tools->sefazConsultaChave($chave);

        $stdCl = new Standardize($response);
        //   //nesse caso $std irá conter uma representação em stdClass do XML
        // $std = $stdCl->toStd();
        //   //nesse caso o $arr irá conter uma representação em array do XML
        $arr = $stdCl->toArray();
        //   //nesse caso o $json irá conter uma representação em JSON do XML
        // $json = $stdCl->toJson();
        return $arr;
    }

    public function consultar($documento)
    {
        try {

            $this->tools->model('65');

            $chave = $documento->chave;
            $response = $this->tools->sefazConsultaChave($chave);

            $stdCl = new Standardize($response);
            $arr = $stdCl->toArray();

            // $arr = json_decode($json);
            return $arr;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function inutilizar($nIni, $nFin, $justificativa){
        try{

            $nSerie = 1;
            $xJust = $justificativa;
            $this->tools->model('65');
            $response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();

            return $arr;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function cancelar($documento, $justificativa)
    {
        try {


            $chave = $documento->chave;
            $response = $this->tools->sefazConsultaChave($chave);
            $stdCl = new Standardize($response);
            $arr = $stdCl->toArray();
            $xJust = $justificativa;

            $nProt = $arr['protNFe']['infProt']['nProt'];

            $response = $this->tools->sefazCancela($chave, $xJust, $nProt);

            $stdCl = new Standardize($response);

            $std = $stdCl->toStd();

            $arr = $stdCl->toArray();

            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    file_put_contents('public/xml_nfce_cancelada/' . $chave . '.xml', $xml);
                    return $arr;
                } else {
                    return $arr;
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sign($xml)
    {
        return $this->tools->signNFe($xml);
    }

    public function transmitir($signXml, $chave)
    {
        try{
            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            
            //método assincrono
            $resp = $this->tools->sefazEnviaLote([$signXml], $idLote, 1);
            sleep(2);
            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 103 && $std->cStat != 104) {
                return "[$std->cStat] - $std->xMotivo";
            }
            sleep(2);

            // $recibo = $std->infRec->nRec; 
            // $protocolo = $this->tools->sefazConsultaRecibo($recibo);
            // sleep(3);

            $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
            try {
                $xml = Complements::toAuthorize($signXml, $resp);
                file_put_contents('public/xml_nfce/' . $chave . '.xml', $xml);
                return $std->protNFe->infProt->nProt;
                // $this->printDanfe($xml);
            } catch (\Exception $e) {
                return $st->toArray($resp);
            }
        } catch (\Exception $e) {
            return 'ERRO ' . $e->getMessage();
        }
    }
}
