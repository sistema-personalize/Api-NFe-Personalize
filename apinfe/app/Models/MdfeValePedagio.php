<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MdfeValePedagio extends Model {

	protected $table = 'api_mdfe_vale_pedagios';

	protected $fillable = [
		'cnpj_contratante', 'cnpj_fornecedor_pagador', 'numero_compra', 'valor', 'mdfe_id'
	];

	protected $hidden = [
		'created_at', 'updated_at'
	];

}