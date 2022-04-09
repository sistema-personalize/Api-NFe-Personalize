-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23-Mar-2022 às 22:44
-- Versão do servidor: 10.4.21-MariaDB
-- versão do PHP: 7.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `curso`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_certificados`
--

CREATE TABLE `api_certificados` (
  `id` int(11) NOT NULL,
  `arquivo` blob DEFAULT NULL,
  `senha` varchar(10) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `path_arquivo` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_ctes`
--

CREATE TABLE `api_ctes` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `ambiente` int(11) NOT NULL,
  `natureza_operacao` varchar(80) NOT NULL,
  `cfop` varchar(4) NOT NULL,
  `codigo_mun_envio` varchar(8) NOT NULL,
  `nome_municipio_envio` varchar(30) NOT NULL,
  `uf_municipio_envio` varchar(2) NOT NULL,
  `codigo_municipio_inicio` varchar(9) NOT NULL,
  `nome_municipio_inicio` varchar(30) NOT NULL,
  `uf_municipio_inicio` varchar(2) NOT NULL,
  `codigo_municipio_fim` varchar(9) NOT NULL,
  `nome_municipio_fim` varchar(30) NOT NULL,
  `uf_municipio_fim` varchar(2) NOT NULL,
  `modal` varchar(4) NOT NULL,
  `retira` int(11) NOT NULL,
  `detalhes_retira` varchar(100) NOT NULL,
  `tomador` int(11) NOT NULL,
  `cst` varchar(3) NOT NULL,
  `perc_icms` decimal(5,2) NOT NULL,
  `data_prevista_entrega` varchar(10) NOT NULL,
  `valor_transporte` decimal(10,2) NOT NULL,
  `valor_receber` decimal(10,2) NOT NULL,
  `produto_predominante` varchar(50) NOT NULL,
  `valor_carga` decimal(10,2) NOT NULL,
  `rntrc` varchar(8) NOT NULL,
  `chave` varchar(54) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `sequencia_correcao` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_chave_nfe`
--

CREATE TABLE `api_cte_chave_nfe` (
  `id` int(11) NOT NULL,
  `chave` varchar(44) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_componentes`
--

CREATE TABLE `api_cte_componentes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_destinatarios`
--

CREATE TABLE `api_cte_destinatarios` (
  `id` int(11) NOT NULL,
  `cpf_cnpj` varchar(18) NOT NULL,
  `ie_rg` varchar(15) NOT NULL,
  `razao_social` varchar(80) NOT NULL,
  `nome_fantasia` varchar(80) NOT NULL,
  `fone` varchar(15) NOT NULL,
  `email` varchar(30) NOT NULL,
  `logradouro` varchar(50) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `complemento` varchar(50) NOT NULL,
  `codigo_municipio_ibge` varchar(9) NOT NULL,
  `nome_municipio` varchar(40) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_doc_outros`
--

CREATE TABLE `api_cte_doc_outros` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `data_emissao` varchar(10) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_emitentes`
--

CREATE TABLE `api_cte_emitentes` (
  `id` int(11) NOT NULL,
  `razao_social` varchar(80) NOT NULL,
  `nome_fantasia` varchar(80) NOT NULL,
  `ie` varchar(15) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `logradouro` varchar(80) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(50) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `nome_municipio` varchar(40) NOT NULL,
  `cod_municipio_ibge` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_endereco_tomador`
--

CREATE TABLE `api_cte_endereco_tomador` (
  `id` int(11) NOT NULL,
  `logradouro` varchar(80) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `complemento` varchar(50) NOT NULL,
  `codigo_municipio_ibge` varchar(9) NOT NULL,
  `nome_municipio` varchar(40) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_medidas`
--

CREATE TABLE `api_cte_medidas` (
  `id` int(11) NOT NULL,
  `cod_unidade` varchar(3) NOT NULL,
  `tipo_medida` varchar(25) NOT NULL,
  `quantidade_carga` decimal(15,4) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_cte_remetentes`
--

CREATE TABLE `api_cte_remetentes` (
  `id` int(11) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `ie` varchar(15) NOT NULL,
  `razao_social` varchar(80) NOT NULL,
  `nome_fantasia` varchar(80) NOT NULL,
  `fone` varchar(12) NOT NULL,
  `email` varchar(50) NOT NULL,
  `logradouro` varchar(80) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `complemento` varchar(50) NOT NULL,
  `nome_municipio` varchar(40) NOT NULL,
  `codigo_municipio_ibge` varchar(9) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cte_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_destinatarios`
--

CREATE TABLE `api_destinatarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `tipo` char(1) NOT NULL,
  `cpf_cnpj` varchar(18) NOT NULL,
  `ie_rg` varchar(20) NOT NULL,
  `contribuinte` int(11) NOT NULL,
  `logradouro` varchar(120) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(40) NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `nome_municipio` varchar(50) NOT NULL,
  `cod_municipio_ibge` varchar(8) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `nome_pais` varchar(10) NOT NULL,
  `cod_pais` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_destinatarios_nfce`
--

CREATE TABLE `api_destinatarios_nfce` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `tipo` char(1) NOT NULL,
  `cpf_cnpj` varchar(18) NOT NULL,
  `ie_rg` varchar(12) NOT NULL,
  `contribuinte` int(11) NOT NULL,
  `logradouro` varchar(120) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(40) NOT NULL,
  `bairro` varchar(50) NOT NULL,
  `nome_municipio` varchar(50) NOT NULL,
  `cod_municipio_ibge` varchar(8) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `nome_pais` varchar(10) NOT NULL,
  `cod_pais` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_documentos`
--

CREATE TABLE `api_documentos` (
  `id` int(11) NOT NULL,
  `comentario` varchar(80) NOT NULL,
  `identificacao` int(11) NOT NULL,
  `numero_nf` int(11) NOT NULL,
  `natureza_operacao` varchar(100) NOT NULL,
  `numero_serie` int(11) NOT NULL,
  `ambiente` int(11) NOT NULL,
  `info_complementar` varchar(100) NOT NULL,
  `consumidor_final` int(11) NOT NULL,
  `operacao_interestadual` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `chave` varchar(44) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `sequencia_correcao` int(11) NOT NULL,
  `aut_xml` varchar(18) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_documentos_nfce`
--

CREATE TABLE `api_documentos_nfce` (
  `id` int(11) NOT NULL,
  `comentario` varchar(80) NOT NULL,
  `identificacao` int(11) NOT NULL,
  `numero_nfce` int(11) NOT NULL,
  `natureza_operacao` varchar(100) NOT NULL,
  `numero_serie` int(11) NOT NULL,
  `ambiente` int(11) NOT NULL,
  `info_complementar` varchar(100) NOT NULL,
  `consumidor_final` int(11) NOT NULL,
  `operacao_interestadual` int(11) NOT NULL,
  `CSC` varchar(40) NOT NULL,
  `CSCid` varchar(10) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `chave` varchar(44) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_duplicatas`
--

CREATE TABLE `api_duplicatas` (
  `id` int(11) NOT NULL,
  `data_vencimento` varchar(10) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_emitentes`
--

CREATE TABLE `api_emitentes` (
  `id` int(11) NOT NULL,
  `codigo_uf` int(11) NOT NULL,
  `razao_social` varchar(120) NOT NULL,
  `nome_fantasia` varchar(120) NOT NULL,
  `ie` varchar(20) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `crt` int(11) NOT NULL,
  `logradouro` varchar(120) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(30) NOT NULL,
  `bairro` varchar(40) NOT NULL,
  `nome_municipio` varchar(50) NOT NULL,
  `cod_municipio_ibge` varchar(8) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `nome_pais` varchar(10) NOT NULL,
  `cod_pais` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_emitentes_nfce`
--

CREATE TABLE `api_emitentes_nfce` (
  `id` int(11) NOT NULL,
  `codigo_uf` int(11) NOT NULL,
  `razao_social` varchar(120) NOT NULL,
  `nome_fantasia` varchar(120) NOT NULL,
  `ie` varchar(12) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `crt` int(11) NOT NULL,
  `logradouro` varchar(120) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(30) NOT NULL,
  `bairro` varchar(40) NOT NULL,
  `nome_municipio` varchar(50) NOT NULL,
  `cod_municipio_ibge` varchar(8) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `nome_pais` varchar(10) NOT NULL,
  `cod_pais` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_endereco_tomador`
--

CREATE TABLE `api_endereco_tomador` (
  `id` int(11) NOT NULL,
  `logradouro` varchar(60) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `complemento` varchar(50) NOT NULL,
  `codigo_municipio` varchar(9) NOT NULL,
  `nome_municipio` varchar(30) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cte_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_faturas`
--

CREATE TABLE `api_faturas` (
  `id` int(11) NOT NULL,
  `desconto` decimal(10,2) NOT NULL,
  `total_nf` decimal(10,2) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_fretes`
--

CREATE TABLE `api_fretes` (
  `id` int(11) NOT NULL,
  `modelo` varchar(2) NOT NULL,
  `quantidade_volumes` decimal(10,2) NOT NULL,
  `especie` varchar(5) NOT NULL,
  `placa` varchar(7) NOT NULL,
  `uf_placa` varchar(2) NOT NULL,
  `peso_liquido` decimal(10,2) NOT NULL,
  `peso_bruto` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `documento_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `numero_volumes` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_item_manifestos`
--

CREATE TABLE `api_item_manifestos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(15) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `codigo_barras` varchar(14) NOT NULL,
  `cfop` varchar(4) NOT NULL,
  `ncm` varchar(8) NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `quantidade` decimal(12,2) NOT NULL,
  `manifesto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_itens`
--

CREATE TABLE `api_itens` (
  `id` int(11) NOT NULL,
  `cod_barras` varchar(13) NOT NULL,
  `codigo_produto` varchar(10) NOT NULL,
  `nome_produto` varchar(80) NOT NULL,
  `ncm` varchar(8) NOT NULL,
  `unidade` varchar(4) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  `compoe_valor_total` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cfop` varchar(4) NOT NULL,
  `cst_csosn` varchar(3) NOT NULL,
  `cst_pis` varchar(3) NOT NULL,
  `cst_cofins` varchar(3) NOT NULL,
  `cst_ipi` varchar(3) NOT NULL,
  `perc_icms` decimal(5,2) NOT NULL,
  `perc_pis` decimal(5,2) NOT NULL,
  `perc_cofins` decimal(5,2) NOT NULL,
  `perc_ipi` decimal(5,2) NOT NULL,
  `vBCSTRet` decimal(5,2) NOT NULL,
  `pST` decimal(5,2) NOT NULL,
  `vICMSSTRet` decimal(5,2) NOT NULL,
  `vICMSSubstituto` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_itens_nfce`
--

CREATE TABLE `api_itens_nfce` (
  `id` int(11) NOT NULL,
  `cod_barras` varchar(13) NOT NULL,
  `codigo_produto` varchar(10) NOT NULL,
  `nome_produto` varchar(80) NOT NULL,
  `ncm` varchar(8) NOT NULL,
  `unidade` varchar(4) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  `compoe_valor_total` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cfop` varchar(4) NOT NULL,
  `cst_csosn` varchar(3) NOT NULL,
  `cst_pis` varchar(3) NOT NULL,
  `cst_cofins` varchar(3) NOT NULL,
  `cst_ipi` varchar(3) NOT NULL,
  `perc_icms` decimal(5,2) NOT NULL,
  `perc_pis` decimal(5,2) NOT NULL,
  `perc_cofins` decimal(5,2) NOT NULL,
  `perc_ipi` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `api_itens_nfce`
--

INSERT INTO `api_itens_nfce` (`id`, `cod_barras`, `codigo_produto`, `nome_produto`, `ncm`, `unidade`, `quantidade`, `valor_unitario`, `compoe_valor_total`, `documento_id`, `created_at`, `updated_at`, `cfop`, `cst_csosn`, `cst_pis`, `cst_cofins`, `cst_ipi`, `perc_icms`, `perc_pis`, `perc_cofins`, `perc_ipi`) VALUES
(1, 'SEM GTIN', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 1, '2022-01-06 11:37:09', '2022-01-06 11:37:09', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(2, 'SEM GTIN', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 2, '2022-01-06 19:38:05', '2022-01-06 19:38:05', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(3, 'SEM GTIN', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 3, '2022-01-06 21:07:20', '2022-01-06 21:07:20', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(4, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 4, '2022-01-06 23:12:45', '2022-01-06 23:12:45', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(5, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 5, '2022-01-07 07:53:11', '2022-01-07 07:53:11', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(6, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 6, '2022-01-10 07:54:34', '2022-01-10 07:54:34', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(7, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 7, '2022-01-10 07:55:50', '2022-01-10 07:55:50', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(8, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 8, '2022-01-10 09:23:43', '2022-01-10 09:23:43', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(9, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 9, '2022-01-10 12:46:04', '2022-01-10 12:46:04', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00'),
(10, '7898011972859', '1', 'Caneta para emissao fiscal', '96083000', 'UN', '1.00', '0.50', 1, 10, '2022-01-10 12:54:12', '2022-01-10 12:54:12', '5102', '102', '49', '49', '99', '0.00', '0.00', '0.00', '0.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_manifestos`
--

CREATE TABLE `api_manifestos` (
  `id` int(11) NOT NULL,
  `chave` varchar(44) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `documento` varchar(14) NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `num_prot` varchar(25) NOT NULL,
  `data_emissao` varchar(10) NOT NULL,
  `sequencia_evento` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `nsu` int(11) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `razao_social` varchar(80) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfes`
--

CREATE TABLE `api_mdfes` (
  `id` int(11) NOT NULL,
  `chave` varchar(55) NOT NULL,
  `protocolo` varchar(20) NOT NULL,
  `ambiente` int(11) NOT NULL,
  `estado` varchar(15) NOT NULL,
  `numero` int(11) NOT NULL,
  `uf_inicio` varchar(2) NOT NULL,
  `uf_fim` varchar(2) NOT NULL,
  `data_inicio_viagem` varchar(10) NOT NULL,
  `carga_posterior` int(11) NOT NULL,
  `cnpj_contratante` varchar(18) NOT NULL,
  `valor_carga` decimal(10,2) NOT NULL,
  `quantidade_carga` decimal(15,4) NOT NULL,
  `info_complementar` varchar(100) NOT NULL,
  `info_adicional_fisco` varchar(100) NOT NULL,
  `condutor_nome` varchar(35) NOT NULL,
  `condutor_cpf` varchar(14) NOT NULL,
  `lacre_rodoviario` varchar(10) NOT NULL,
  `tipo_emitente` int(11) NOT NULL,
  `tipo_transporte` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_chave_cte`
--

CREATE TABLE `api_mdfe_chave_cte` (
  `id` int(11) NOT NULL,
  `chave` varchar(44) NOT NULL,
  `info_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_chave_nfe`
--

CREATE TABLE `api_mdfe_chave_nfe` (
  `id` int(11) NOT NULL,
  `chave` varchar(44) NOT NULL,
  `info_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_ciot`
--

CREATE TABLE `api_mdfe_ciot` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `cpf_cnpj` varchar(18) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_emitentes`
--

CREATE TABLE `api_mdfe_emitentes` (
  `id` int(11) NOT NULL,
  `razao_social` varchar(80) NOT NULL,
  `nome_fantasia` varchar(80) NOT NULL,
  `ie` varchar(15) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `logradouro` varchar(80) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(30) NOT NULL,
  `nome_municipio` varchar(35) NOT NULL,
  `cod_municipio_ibge` varchar(9) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `inscricao_municipal` varchar(15) NOT NULL,
  `complemento` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_info_descargas`
--

CREATE TABLE `api_mdfe_info_descargas` (
  `id` int(11) NOT NULL,
  `nome_municipio` varchar(40) NOT NULL,
  `cod_municipio_ibge` varchar(9) NOT NULL,
  `id_unidade_carga` varchar(10) NOT NULL,
  `quantidade_rateio` decimal(5,2) NOT NULL,
  `tipo_unidade_transporte` int(11) NOT NULL,
  `id_unidade_transporte` varchar(20) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_lacre_transportes`
--

CREATE TABLE `api_mdfe_lacre_transportes` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `info_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_lacre_unidade_cargas`
--

CREATE TABLE `api_mdfe_lacre_unidade_cargas` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `info_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_municipio_carregamentos`
--

CREATE TABLE `api_mdfe_municipio_carregamentos` (
  `id` int(11) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `codigo_municipio_ibge` varchar(9) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_percursos`
--

CREATE TABLE `api_mdfe_percursos` (
  `id` int(11) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_seguradoras`
--

CREATE TABLE `api_mdfe_seguradoras` (
  `id` int(11) NOT NULL,
  `nome` varchar(40) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `numero_apolice` varchar(15) NOT NULL,
  `numero_averbacao` varchar(40) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_vale_pedagios`
--

CREATE TABLE `api_mdfe_vale_pedagios` (
  `id` int(11) NOT NULL,
  `cnpj_contratante` varchar(18) NOT NULL,
  `cnpj_fornecedor_pagador` varchar(18) NOT NULL,
  `numero_compra` varchar(20) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_mdfe_veiculos`
--

CREATE TABLE `api_mdfe_veiculos` (
  `id` int(11) NOT NULL,
  `rntrc` varchar(12) NOT NULL,
  `placa` varchar(8) NOT NULL,
  `uf` varchar(2) NOT NULL,
  `tara` decimal(12,4) NOT NULL,
  `capacidade` decimal(12,4) NOT NULL,
  `tipo_rodado` varchar(2) NOT NULL,
  `tipo_carroceira` varchar(2) NOT NULL,
  `nome_proprietario` varchar(50) NOT NULL,
  `cpf_cnpj_proprietario` varchar(18) NOT NULL,
  `ie_proprietario` varchar(15) NOT NULL,
  `tipo_proprietario` int(11) NOT NULL,
  `uf_proprietario` varchar(2) NOT NULL,
  `tipo_veiculo` varchar(1) NOT NULL,
  `mdfe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_pagamentos`
--

CREATE TABLE `api_pagamentos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(2) NOT NULL,
  `indicacao_pagamento` varchar(2) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_pagamentos_nfce`
--

CREATE TABLE `api_pagamentos_nfce` (
  `id` int(11) NOT NULL,
  `tipo` varchar(2) NOT NULL,
  `indicacao_pagamento` varchar(2) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_resp_tecnicos`
--

CREATE TABLE `api_resp_tecnicos` (
  `id` int(11) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `contato` varchar(30) NOT NULL,
  `email` varchar(40) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_resp_tecnicos_nfce`
--

CREATE TABLE `api_resp_tecnicos_nfce` (
  `id` int(11) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `contato` varchar(30) NOT NULL,
  `email` varchar(40) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_tributacoes_nfce`
--

CREATE TABLE `api_tributacoes_nfce` (
  `id` int(11) NOT NULL,
  `icms` decimal(4,2) NOT NULL,
  `pis` decimal(4,2) NOT NULL,
  `cofins` decimal(4,2) NOT NULL,
  `ipi` decimal(4,2) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `api_certificados`
--
ALTER TABLE `api_certificados`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_ctes`
--
ALTER TABLE `api_ctes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_cte_chave_nfe`
--
ALTER TABLE `api_cte_chave_nfe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_componentes`
--
ALTER TABLE `api_cte_componentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_destinatarios`
--
ALTER TABLE `api_cte_destinatarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_doc_outros`
--
ALTER TABLE `api_cte_doc_outros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_emitentes`
--
ALTER TABLE `api_cte_emitentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_endereco_tomador`
--
ALTER TABLE `api_cte_endereco_tomador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_medidas`
--
ALTER TABLE `api_cte_medidas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_cte_remetentes`
--
ALTER TABLE `api_cte_remetentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cte_id` (`cte_id`);

--
-- Índices para tabela `api_destinatarios`
--
ALTER TABLE `api_destinatarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_destinatarios_nfce`
--
ALTER TABLE `api_destinatarios_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_documentos`
--
ALTER TABLE `api_documentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_documentos_nfce`
--
ALTER TABLE `api_documentos_nfce`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_duplicatas`
--
ALTER TABLE `api_duplicatas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_emitentes`
--
ALTER TABLE `api_emitentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_emitentes_nfce`
--
ALTER TABLE `api_emitentes_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_endereco_tomador`
--
ALTER TABLE `api_endereco_tomador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `api_endereco_tomador_ibfk_1` (`cte_id`);

--
-- Índices para tabela `api_faturas`
--
ALTER TABLE `api_faturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_fretes`
--
ALTER TABLE `api_fretes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_item_manifestos`
--
ALTER TABLE `api_item_manifestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manifesto_id` (`manifesto_id`);

--
-- Índices para tabela `api_itens`
--
ALTER TABLE `api_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_itens_nfce`
--
ALTER TABLE `api_itens_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_manifestos`
--
ALTER TABLE `api_manifestos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_mdfes`
--
ALTER TABLE `api_mdfes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `api_mdfe_chave_cte`
--
ALTER TABLE `api_mdfe_chave_cte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `info_id` (`info_id`);

--
-- Índices para tabela `api_mdfe_chave_nfe`
--
ALTER TABLE `api_mdfe_chave_nfe`
  ADD KEY `info_id` (`info_id`);

--
-- Índices para tabela `api_mdfe_ciot`
--
ALTER TABLE `api_mdfe_ciot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_emitentes`
--
ALTER TABLE `api_mdfe_emitentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_info_descargas`
--
ALTER TABLE `api_mdfe_info_descargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_lacre_transportes`
--
ALTER TABLE `api_mdfe_lacre_transportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `info_id` (`info_id`);

--
-- Índices para tabela `api_mdfe_lacre_unidade_cargas`
--
ALTER TABLE `api_mdfe_lacre_unidade_cargas`
  ADD KEY `info_id` (`info_id`);

--
-- Índices para tabela `api_mdfe_municipio_carregamentos`
--
ALTER TABLE `api_mdfe_municipio_carregamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_percursos`
--
ALTER TABLE `api_mdfe_percursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_seguradoras`
--
ALTER TABLE `api_mdfe_seguradoras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_vale_pedagios`
--
ALTER TABLE `api_mdfe_vale_pedagios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_mdfe_veiculos`
--
ALTER TABLE `api_mdfe_veiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mdfe_id` (`mdfe_id`);

--
-- Índices para tabela `api_pagamentos`
--
ALTER TABLE `api_pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_pagamentos_nfce`
--
ALTER TABLE `api_pagamentos_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_resp_tecnicos`
--
ALTER TABLE `api_resp_tecnicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_resp_tecnicos_nfce`
--
ALTER TABLE `api_resp_tecnicos_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices para tabela `api_tributacoes_nfce`
--
ALTER TABLE `api_tributacoes_nfce`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `api_certificados`
--
ALTER TABLE `api_certificados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `api_ctes`
--
ALTER TABLE `api_ctes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_chave_nfe`
--
ALTER TABLE `api_cte_chave_nfe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_componentes`
--
ALTER TABLE `api_cte_componentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_destinatarios`
--
ALTER TABLE `api_cte_destinatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_doc_outros`
--
ALTER TABLE `api_cte_doc_outros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_emitentes`
--
ALTER TABLE `api_cte_emitentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_endereco_tomador`
--
ALTER TABLE `api_cte_endereco_tomador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_medidas`
--
ALTER TABLE `api_cte_medidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_cte_remetentes`
--
ALTER TABLE `api_cte_remetentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_destinatarios`
--
ALTER TABLE `api_destinatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_destinatarios_nfce`
--
ALTER TABLE `api_destinatarios_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_documentos`
--
ALTER TABLE `api_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `api_documentos_nfce`
--
ALTER TABLE `api_documentos_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_duplicatas`
--
ALTER TABLE `api_duplicatas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_emitentes`
--
ALTER TABLE `api_emitentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_emitentes_nfce`
--
ALTER TABLE `api_emitentes_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_endereco_tomador`
--
ALTER TABLE `api_endereco_tomador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_faturas`
--
ALTER TABLE `api_faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_fretes`
--
ALTER TABLE `api_fretes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_item_manifestos`
--
ALTER TABLE `api_item_manifestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_itens`
--
ALTER TABLE `api_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de tabela `api_itens_nfce`
--
ALTER TABLE `api_itens_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_manifestos`
--
ALTER TABLE `api_manifestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfes`
--
ALTER TABLE `api_mdfes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_chave_cte`
--
ALTER TABLE `api_mdfe_chave_cte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_ciot`
--
ALTER TABLE `api_mdfe_ciot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_emitentes`
--
ALTER TABLE `api_mdfe_emitentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `api_mdfe_info_descargas`
--
ALTER TABLE `api_mdfe_info_descargas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `api_mdfe_lacre_transportes`
--
ALTER TABLE `api_mdfe_lacre_transportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_municipio_carregamentos`
--
ALTER TABLE `api_mdfe_municipio_carregamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `api_mdfe_percursos`
--
ALTER TABLE `api_mdfe_percursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_seguradoras`
--
ALTER TABLE `api_mdfe_seguradoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_vale_pedagios`
--
ALTER TABLE `api_mdfe_vale_pedagios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_mdfe_veiculos`
--
ALTER TABLE `api_mdfe_veiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_pagamentos`
--
ALTER TABLE `api_pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_pagamentos_nfce`
--
ALTER TABLE `api_pagamentos_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_resp_tecnicos`
--
ALTER TABLE `api_resp_tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `api_resp_tecnicos_nfce`
--
ALTER TABLE `api_resp_tecnicos_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `api_tributacoes_nfce`
--
ALTER TABLE `api_tributacoes_nfce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `api_cte_chave_nfe`
--
ALTER TABLE `api_cte_chave_nfe`
  ADD CONSTRAINT `api_cte_chave_nfe_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_componentes`
--
ALTER TABLE `api_cte_componentes`
  ADD CONSTRAINT `api_cte_componentes_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_destinatarios`
--
ALTER TABLE `api_cte_destinatarios`
  ADD CONSTRAINT `api_cte_destinatarios_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_doc_outros`
--
ALTER TABLE `api_cte_doc_outros`
  ADD CONSTRAINT `api_cte_doc_outros_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_emitentes`
--
ALTER TABLE `api_cte_emitentes`
  ADD CONSTRAINT `api_cte_emitentes_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_endereco_tomador`
--
ALTER TABLE `api_cte_endereco_tomador`
  ADD CONSTRAINT `api_cte_endereco_tomador_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_medidas`
--
ALTER TABLE `api_cte_medidas`
  ADD CONSTRAINT `api_cte_medidas_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_cte_remetentes`
--
ALTER TABLE `api_cte_remetentes`
  ADD CONSTRAINT `api_cte_remetentes_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_destinatarios`
--
ALTER TABLE `api_destinatarios`
  ADD CONSTRAINT `api_destinatarios_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_emitentes`
--
ALTER TABLE `api_emitentes`
  ADD CONSTRAINT `api_emitentes_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_endereco_tomador`
--
ALTER TABLE `api_endereco_tomador`
  ADD CONSTRAINT `api_endereco_tomador_ibfk_1` FOREIGN KEY (`cte_id`) REFERENCES `api_ctes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_faturas`
--
ALTER TABLE `api_faturas`
  ADD CONSTRAINT `api_faturas_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_fretes`
--
ALTER TABLE `api_fretes`
  ADD CONSTRAINT `api_fretes_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_item_manifestos`
--
ALTER TABLE `api_item_manifestos`
  ADD CONSTRAINT `api_item_manifestos_ibfk_1` FOREIGN KEY (`manifesto_id`) REFERENCES `api_manifestos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_itens`
--
ALTER TABLE `api_itens`
  ADD CONSTRAINT `api_itens_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_mdfe_chave_cte`
--
ALTER TABLE `api_mdfe_chave_cte`
  ADD CONSTRAINT `api_mdfe_chave_cte_ibfk_1` FOREIGN KEY (`info_id`) REFERENCES `api_mdfe_info_descargas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_chave_nfe`
--
ALTER TABLE `api_mdfe_chave_nfe`
  ADD CONSTRAINT `api_mdfe_chave_nfe_ibfk_1` FOREIGN KEY (`info_id`) REFERENCES `api_mdfe_info_descargas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_ciot`
--
ALTER TABLE `api_mdfe_ciot`
  ADD CONSTRAINT `api_mdfe_ciot_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_emitentes`
--
ALTER TABLE `api_mdfe_emitentes`
  ADD CONSTRAINT `api_mdfe_emitentes_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_info_descargas`
--
ALTER TABLE `api_mdfe_info_descargas`
  ADD CONSTRAINT `api_mdfe_info_descargas_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_lacre_transportes`
--
ALTER TABLE `api_mdfe_lacre_transportes`
  ADD CONSTRAINT `api_mdfe_lacre_transportes_ibfk_1` FOREIGN KEY (`info_id`) REFERENCES `api_mdfe_info_descargas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_lacre_unidade_cargas`
--
ALTER TABLE `api_mdfe_lacre_unidade_cargas`
  ADD CONSTRAINT `api_mdfe_lacre_unidade_cargas_ibfk_1` FOREIGN KEY (`info_id`) REFERENCES `api_mdfe_info_descargas` (`id`);

--
-- Limitadores para a tabela `api_mdfe_municipio_carregamentos`
--
ALTER TABLE `api_mdfe_municipio_carregamentos`
  ADD CONSTRAINT `api_mdfe_municipio_carregamentos_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_percursos`
--
ALTER TABLE `api_mdfe_percursos`
  ADD CONSTRAINT `api_mdfe_percursos_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_seguradoras`
--
ALTER TABLE `api_mdfe_seguradoras`
  ADD CONSTRAINT `api_mdfe_seguradoras_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_vale_pedagios`
--
ALTER TABLE `api_mdfe_vale_pedagios`
  ADD CONSTRAINT `api_mdfe_vale_pedagios_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_mdfe_veiculos`
--
ALTER TABLE `api_mdfe_veiculos`
  ADD CONSTRAINT `api_mdfe_veiculos_ibfk_1` FOREIGN KEY (`mdfe_id`) REFERENCES `api_mdfes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `api_pagamentos`
--
ALTER TABLE `api_pagamentos`
  ADD CONSTRAINT `api_pagamentos_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);

--
-- Limitadores para a tabela `api_resp_tecnicos`
--
ALTER TABLE `api_resp_tecnicos`
  ADD CONSTRAINT `api_resp_tecnicos_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `api_documentos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
