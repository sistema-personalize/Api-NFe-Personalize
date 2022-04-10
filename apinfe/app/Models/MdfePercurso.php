<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfePercurso extends Model {

	protected $table = 'api_mdfe_percursos';

	protected $fillable = [
		'uf', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

	
}