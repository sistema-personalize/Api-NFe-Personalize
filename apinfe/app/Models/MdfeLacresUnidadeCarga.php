<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeLacresUnidadeCarga extends Model {

	protected $table = 'api_mdfe_lacre_unidade_cargas';

	protected $fillable = [
		'numero', 'info_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}