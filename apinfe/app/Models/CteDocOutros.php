<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CteDocOutros extends Model {

	protected $table = 'api_cte_doc_outros';

	protected $fillable = [
		'tipo', 'descricao', 'numero', 'data_emissao', 'valor', 'cte_id' 
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}