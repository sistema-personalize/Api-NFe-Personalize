<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ItemManifesto extends Model {

	protected $table = 'api_item_manifestos';

	protected $fillable = [
		'codigo', 'nome', 'codigo_barras', 'cfop', 'ncm', 'valor', 'quantidade', 'manifesto_id' 
	];


	protected $hidden = [
		'created_at', 'updated_at'
	];

	

}