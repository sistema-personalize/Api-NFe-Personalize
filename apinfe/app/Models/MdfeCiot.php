<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeCiot extends Model {

	protected $table = 'api_mdfe_ciot';

	protected $fillable = [
		'codigo', 'cpf_cnpj', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

	
}