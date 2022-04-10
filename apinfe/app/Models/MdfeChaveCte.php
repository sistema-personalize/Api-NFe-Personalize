<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeChaveCte extends Model {

	protected $table = 'api_mdfe_chave_cte';

	protected $fillable = [
		'chave', 'info_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}