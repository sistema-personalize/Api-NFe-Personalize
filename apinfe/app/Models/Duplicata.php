<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Duplicata extends Model {

	protected $table = 'api_duplicatas';

	protected $fillable = [
		'data_vencimento', 'valor', 'documento_id'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}