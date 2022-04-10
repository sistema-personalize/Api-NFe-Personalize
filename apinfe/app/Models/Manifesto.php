<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Manifesto extends Model {

	protected $table = 'api_manifestos';

	 protected $fillable = [
		'chave', 'nome', 'documento', 'valor', 'num_prot', 'data_emissao', 
		'sequencia_evento', 'tipo', 'nsu', 'cnpj', 'razao_social', 'uf'
	];

	public function estado(){
		if($this->tipo == 0){
			return "--";
		}else if($this->tipo == 1){
			return "Ciência";
		}else if($this->tipo == 2){
			return "Confirmada";
		}else if($this->tipo == 2){
			return "Desconhecimento";
		}else if($this->tipo == 2){
			return "Operação não realizada";
		}
	}

	protected $hidden = [
		'created_at', 'updated_at'
	];

}