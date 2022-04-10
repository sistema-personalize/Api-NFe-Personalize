<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteMedida extends Model {

	protected $table = 'api_cte_medidas';

	protected $fillable = [
		'cod_unidade', 'tipo_medida', 'quantidade_carga', 'cte_id' 
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}