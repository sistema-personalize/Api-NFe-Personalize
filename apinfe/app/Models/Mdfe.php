<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mdfe extends Model {

	protected $table = 'api_mdfes';

	protected $fillable = [
		'chave', 'estado', 'numero', 'ambiente', 'uf_inicio', 'uf_fim', 'data_inicio_viagem', 
		'carga_posterior', 'protocolo',
		'cnpj_contratante', 'valor_carga', 'quantidade_carga', 'info_complementar', 
		'info_adicional_fisco', 'condutor_nome', 'condutor_cpf', 'lacre_rodoviario', 'tipo_emitente',
		'tipo_transporte'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

	public function emitente()
	{
		return $this->hasOne('App\Models\MdfeEmitente', 'mdfe_id', 'id');
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