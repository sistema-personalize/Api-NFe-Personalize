<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model {

	protected $table = 'api_pagamentos';

	protected $fillable = [
		'tipo', 'indicacao_pagamento', 'documento_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at'
    ];

}