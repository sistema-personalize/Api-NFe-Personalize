<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model {

	protected $table = 'api_faturas';

	protected $fillable = [
		'desconto', 'total_nf', 'documento_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at'
    ];

}