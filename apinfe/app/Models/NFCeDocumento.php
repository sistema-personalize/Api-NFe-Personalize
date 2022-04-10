<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NFCeDocumento extends Model {

	protected $table = 'api_documentos_nfce';

	protected $fillable = [
		'comentario', 'identificacao', 'numero_nfce', 'natureza_operacao', 'numero_serie',
		'ambiente', 'info_complementar', 'consumidor_final', 'operacao_interestadual', 'CSC',
		'CSCid', 'chave', 'estado'
	];

	public function emitente()
    {
        return $this->hasOne('App\Models\NFCeEmitente', 'documento_id', 'id');
	}
	
	public function destinatario()
    {
        return $this->hasOne('App\Models\NFCeDestinatario', 'documento_id', 'id');
	}
	
	public function itens()
    {
        return $this->hasMany('App\Models\NFCeItem', 'documento_id', 'id');
	}
	
	public function respTecnico()
    {
        return $this->hasOne('App\Models\NFCeResponsavelTecnico', 'documento_id', 'id');
	}

	public function tributacao()
    {
        return $this->hasOne('App\Models\NFCeTributacao', 'documento_id', 'id');
	}

	public function pagamento()
    {
        return $this->hasOne('App\Models\NFCePagamento', 'documento_id', 'id');
	}



}