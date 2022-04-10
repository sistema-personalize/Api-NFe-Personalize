<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteEnderecoTomador extends Model {

	protected $table = 'api_cte_endereco_tomador';

	protected $fillable = [
		'logradouro', 'numero', 'bairro', 'complemento','codigo_municipio_ibge', 'nome_municipio', 
		'cep', 'uf', 'cte_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}