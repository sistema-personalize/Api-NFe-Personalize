<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteDestinatario extends Model {

	protected $table = 'api_cte_destinatarios';

	protected $fillable = [
		'cpf_cnpj', 'ie_rg', 'razao_social', 'nome_fantasia', 'fone', 'email', 'logradouro', 'numero',
		'bairro', 'complemento', 'nome_municipio', 'codigo_municipio_ibge', 'cep', 'uf', 'cte_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}