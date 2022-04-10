<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteRemetente extends Model {

	protected $table = 'api_cte_remetentes';

	protected $fillable = [
		'cnpj', 'ie', 'razao_social', 'nome_fantasia', 'fone', 'email', 'logradouro', 'numero',
		'bairro', 'complemento', 'nome_municipio', 'codigo_municipio_ibge', 'cep', 'uf', 'cte_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}