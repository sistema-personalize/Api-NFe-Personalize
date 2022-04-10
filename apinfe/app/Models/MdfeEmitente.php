<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeEmitente extends Model {

	protected $table = 'api_mdfe_emitentes';

	protected $fillable = [
		'razao_social', 'nome_fantasia', 'ie', 'cnpj', 'logradouro', 'numero', 
		'complemento', 'bairro', 'nome_municipio', 'cod_municipio_ibge', 'uf', 'cep', 'telefone', 
		'mdfe_id', 'inscricao_municipal'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}