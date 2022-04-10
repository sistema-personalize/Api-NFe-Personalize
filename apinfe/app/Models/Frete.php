<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Frete extends Model {

	protected $table = 'api_fretes';

	protected $fillable = [
		'modelo', 'valor', 'quantidade_volumes', 'especie',  'documento_id', 'placa', 'uf_placa',
		'peso_liquido', 'peso_bruto', 'numero_volumes'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}