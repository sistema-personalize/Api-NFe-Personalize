<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeSeguradora extends Model {

	protected $table = 'api_mdfe_seguradoras';

	protected $fillable = [
		'nome', 'cnpj', 'numero_apolice', 'numero_averbacao', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}