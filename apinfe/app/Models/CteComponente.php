<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteComponente extends Model {

	protected $table = 'api_cte_componentes';

	protected $fillable = [
		'nome', 'valor', 'cte_id' 
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}