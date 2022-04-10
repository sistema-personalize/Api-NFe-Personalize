<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NFCeItem extends Model {

	protected $table = 'api_itens_nfce';

	protected $fillable = [
		'cod_barras', 'codigo_produto', 'nome_produto', 'ncm', 'unidade', 'quantidade', 'cfop',
		'valor_unitario', 'compoe_valor_total', 'documento_id', 'cst_csosn', 'cst_pis', 'cst_cofins',
		'cst_ipi', 'perc_icms', 'perc_pis', 'perc_cofins', 'perc_ipi'
	];

	protected $hidden = [
        'created_at', 'updated_at'
    ];

}