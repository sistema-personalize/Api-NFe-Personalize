<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Emitente extends Model {

	protected $table = 'api_emitentes';

	protected $fillable = [
		'codigo_uf', 'razao_social', 'nome_fantasia', 'ie', 'cnpj', 'crt',
		'logradouro', 'numero', 'complemento', 'bairro', 'nome_municipio',
		'cod_municipio_ibge', 'uf', 'cep', 'nome_pais', 'cod_pais', 'documento_id'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}