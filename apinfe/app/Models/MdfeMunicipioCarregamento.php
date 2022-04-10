<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeMunicipioCarregamento extends Model {

	protected $table = 'api_mdfe_municipio_carregamentos';

	protected $fillable = [
		'nome', 'codigo_municipio_ibge', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}