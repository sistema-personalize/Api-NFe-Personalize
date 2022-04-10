<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model {

	protected $table = 'api_documentos';

	protected $fillable = [
		'comentario', 'identificacao', 'numero_nf', 'natureza_operacao', 'numero_serie',
		'ambiente', 'info_complementar', 'consumidor_final', 'operacao_interestadual', 'chave', 'estado', 'sequencia_correcao', 'aut_xml'
	];

	public function emitente()
    {
        return $this->hasOne('App\Models\Emitente', 'documento_id', 'id');
	}
	
	public function destinatario()
    {
        return $this->hasOne('App\Models\Destinatario', 'documento_id', 'id');
	}
	
	public function itens()
    {
        return $this->hasMany('App\Models\Item', 'documento_id', 'id');
	}
	
	public function frete()
    {
        return $this->hasOne('App\Models\Frete', 'documento_id', 'id');
	}

	public function respTecnico()
    {
        return $this->hasOne('App\Models\ResponsavelTecnico', 'documento_id', 'id');
	}

	public function tributacao()
    {
        return $this->hasOne('App\Models\Tributacao', 'documento_id', 'id');
	}

	public function pagamento()
    {
        return $this->hasOne('App\Models\Pagamento', 'documento_id', 'id');
	}

	public function fatura()
    {
        return $this->hasOne('App\Models\Fatura', 'documento_id', 'id');
	}

	public function duplicatas()
    {
        return $this->hasMany('App\Models\Duplicata', 'documento_id', 'id');
	}

}