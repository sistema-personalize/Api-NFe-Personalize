<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cte extends Model {

	protected $table = 'api_ctes';

	protected $fillable = [
		'natureza_operacao', 'numero', 'ambiente', 'cfop', 'codigo_mun_envio', 'nome_municipio_envio',
		'uf_municipio_envio', 'codigo_municipio_inicio', 'nome_municipio_inicio', 'uf_municipio_inicio', 'codigo_municipio_fim', 'nome_municipio_fim', 'uf_municipio_fim', 'modal', 'retira', 
		'detalhes_retira', 'tomador', 'cst', 'perc_icms', 'data_prevista_entrega', 'valor_transporte', 
		'valor_receber', 'produto_predominante', 'valor_carga', 'rntrc', 'chave', 'estado', 'sequencia_correcao'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

	public function emitente()
	{
		return $this->hasOne('App\Models\CteEmitente', 'cte_id', 'id');
	}

	public function destinatario()
	{
		return $this->hasOne('App\Models\CteDestinatario', 'cte_id', 'id');
	}

	public static function estados(){
		return [
			'11' => 'RO',
			'12' => 'AC',
			'13' => 'AM',
			'14' => 'RR',
			'15' => 'PA',
			'16' => 'AP',
			'17' => 'TO',
			'21' => 'MA',
			'22' => 'PI',
			'23' => 'CE',
			'24' => 'RN',
			'25' => 'PB',
			'26' => 'PE',
			'27' => 'AL',
			'28' => 'SE',
			'29' => 'BA',
			'31' => 'MG',
			'32' => 'ES',
			'33' => 'RJ',
			'35' => 'SP',
			'41' => 'PR',
			'42' => 'SC',
			'43' => 'RS',
			'50' => 'MS',
			'51' => 'MT',
			'52' => 'GO',
			'53' => 'DF'
		];
	}

	public static function getCodUF($uf){
		foreach(Cte::estados() as $key => $e){
			if($uf == $e) return $key;
		}
	}

}