<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeLacresTranporte extends Model {

	protected $table = 'api_mdfe_lacre_transportes';

	protected $fillable = [
		'numero', 'info_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}