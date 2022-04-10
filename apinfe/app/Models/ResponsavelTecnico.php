<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResponsavelTecnico extends Model {

	protected $table = 'api_resp_tecnicos';

	protected $fillable = [
		'cnpj', 'contato', 'email', 'telefone', 'documento_id'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}