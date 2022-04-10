<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeInfoDescarga extends Model {

	protected $table = 'api_mdfe_info_descargas';

	protected $fillable = [
		'nome_municipio', 'cod_municipio_ibge', 'id_unidade_carga', 'quantidade_rateio', 
		'tipo_unidade_transporte', 'id_unidade_transporte', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}