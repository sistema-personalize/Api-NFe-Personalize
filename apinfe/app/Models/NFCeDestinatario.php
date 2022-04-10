<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NFCeDestinatario extends Model {

	protected $table = 'api_destinatarios_nfce';

	protected $fillable = [
		'nome', 'tipo', 'cpf_cnpj', 'ie_rg', 'contribuinte',
		'logradouro', 'numero', 'complemento', 'bairro', 'nome_municipio',
		'cod_municipio_ibge', 'uf', 'cep', 'nome_pais', 'cod_pais', 'documento_id'
    ];
    
    protected $hidden = [
        'created_at', 'updated_at'
    ];

}