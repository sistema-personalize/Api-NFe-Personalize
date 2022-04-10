<?php  

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Certificado extends Model {

	protected $table = 'api_certificados';

	protected $fillable = [
		'arquivo',
		'senha'
	];

}