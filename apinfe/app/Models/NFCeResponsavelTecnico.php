<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NFCeResponsavelTecnico extends Model {

	protected $table = 'api_resp_tecnicos_nfce';

	protected $fillable = [
		'cnpj', 'contato', 'email', 'telefone', 'documento_id'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}