<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeChaveNfe extends Model {

	protected $table = 'api_mdfe_chave_nfe';

	protected $fillable = [
		'chave', 'info_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}