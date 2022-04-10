<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteEmitente extends Model {

	protected $table = 'api_cte_emitentes';

	protected $fillable = [
		'razao_social', 'nome_fantasia', 'ie','cnpj', 'logradouro', 'numero', 
		'complemento', 'bairro', 'nome_municipio', 'cod_municipio_ibge', 'uf', 'cep', 'telefone', 'cte_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}