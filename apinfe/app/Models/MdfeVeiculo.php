<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeVeiculo extends Model {

	protected $table = 'api_mdfe_veiculos';

	protected $fillable = [
		'rntrc', 'placa', 'tara', 'capacidade', 'tipo_rodado', 'tipo_carroceira', 'uf', 
		'nome_proprietario', 'cpf_cnpj_proprietario', 'ie_proprietario', 'tipo_proprietario', 
		'uf_proprietario', 'tipo_veiculo', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}