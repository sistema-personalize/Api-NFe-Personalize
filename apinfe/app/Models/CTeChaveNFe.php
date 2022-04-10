<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CTeChaveNFe extends Model {

	protected $table = 'api_cte_chave_nfe';

	protected $fillable = [
		'chave', 'cte_id' 
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}