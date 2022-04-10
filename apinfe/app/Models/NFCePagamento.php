<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NFCePagamento extends Model {

	protected $table = 'api_pagamentos_nfce';

	protected $fillable = [
		'tipo', 'indicacao_pagamento', 'documento_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at'
    ];

}