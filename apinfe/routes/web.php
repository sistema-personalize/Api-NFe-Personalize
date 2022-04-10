<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/certificado/{cnpj}','NFeController@certificado');

$router->group(['prefix' => 'nfe'], function () use ($router) {
    $router->get('/','NFeController@all');
    $router->get('/{id}','NFeController@first');
    $router->get('/xml/{id}','NFeController@xml');

    $router->post('/gerarXml', ['uses' => 'NFeController@gerarXml', 'middleware' => 'valid']);
    $router->post('/danfeTemporaria', ['uses' => 'NFeController@danfeTemporaria', 
        'middleware' => 'valid']);
    $router->post('/transmitir', ['uses' => 'NFeController@transmitir', 'middleware' => 'valid']);

    $router->post('/cancelarPorIdDocumento', 'NFeController@cancelarPorIdDocumento');
    $router->post('/cancelarPorChave', 'NFeController@cancelarPorChave');

    $router->post('/consultarPorIdDocumento', 'NFeController@consultarPorIdDocumento');
    $router->post('/consultarPorChave', 'NFeController@consultarPorChave');

    $router->get('/imprimirPorDocumento/{id}', 'NFeController@imprimirPorDocumento');
    $router->get('/imprimirPorChave/{chave}', 'NFeController@imprimirPorChave');

    $router->post('/correcaoPorIdDocumento', 'NFeController@correcaoPorIdDocumento');
    $router->post('/correcaorPorChave', 'NFeController@correcaorPorChave');

    $router->get('/imprimirCorrecaoPorDocumento/{id}', 'NFeController@imprimirCorrecaoPorDocumento');
    $router->get('/imprimirCorrecaoPorChave/{chave}', 'NFeController@imprimirCorrecaoPorChave');

    $router->get('/imprimirCancelaPorDocumento/{id}', 'NFeController@imprimirCancelaPorDocumento');
    $router->get('/imprimirCancelaPorChave/{chave}', 'NFeController@imprimirCancelaPorChave');

    $router->post('/enviarEmailPorIdDocumento', 'NFeController@enviarEmailPorIdDocumento');
    $router->post('/enviarEmailPorChave', 'NFeController@enviarEmailPorChave');

    $router->post('/inutilizar', 'NFeController@inutilizar');
});


$router->group(['prefix' => 'nfce'], function () use ($router) {
    $router->get('/','NFCeController@all');
    $router->get('/{id}','NFCeController@first');
    $router->get('/xml/{id}','NFCeController@xml');
    $router->post('/gerarXml', ['uses' => 'NFCeController@gerarXml', 'middleware' => 'ValidNFCe']);
    $router->post('/transmitir', 
        ['uses' => 'NFCeController@transmitir', 'middleware' => 'ValidNFCe']);

    $router->post('/cancelarPorIdDocumento', 'NFCeController@cancelarPorIdDocumento');
    $router->post('/cancelarPorChave', 'NFCeController@cancelarPorChave');

    $router->post('/consultarPorIdDocumento', 'NFCeController@consultarPorIdDocumento');
    $router->post('/consultarPorChave', 'NFCeController@consultarPorChave');

    $router->get('/imprimirPorDocumento/{id}', 'NFCeController@imprimirPorDocumento');
    $router->get('/imprimirPorChave/{chave}', 'NFCeController@imprimirPorChave');
    $router->post('/inutilizar', 'NFCeController@inutilizar');
    
});


