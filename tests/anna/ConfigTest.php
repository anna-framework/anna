<?php

use Anna\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{	

	/**
	 * @conver \Anna\Config
	 */
	public function testeConfig()
	{
		$cfg = Config::getInstance();
		$this->assertEquals(Config::class, get_class($cfg), 'Nao foi encontrado instancia de Ann\Config');

		$testParam = "Value of Teste param";

		try {
			$cfg->set('testParam', $testParam);
		} catch (Exception $e) {
			$this->fail('Nao foi possivel escrever valor nas configuracoes');
		}

		try {
			$getTestParam = $cfg->get('testParam');
		} catch (Exception $e) {
			$this->fail('Nao foi possivel ler valor nas configuracoes');
		}

		$this->assertEquals("Value of Teste param", $getTestParam, 'Discrepancia entre os valores de entrada e saida das configuracoes');

		$testArrayParams = [
			'param1' => 'teste1', 
			'param2' => 'teste2', 
			'param3' => 'teste3', 
			'param4' => 'teste4'	
		];

		try{
			$cfg->addConfigs($testArrayParams);
		} catch (Exception $e) {
			$this->fail('Nao foi possivel escrever array de valores nas configuracoes');
		}

		$this->assertEquals('teste1', $cfg->get('param1'), 'O parametro 1 nao foi devidamente salvo');
		$this->assertEquals('teste2', $cfg->get('param2'), 'O parametro 2 nao foi devidamente salvo');
		$this->assertEquals('teste3', $cfg->get('param3'), 'O parametro 3 nao foi devidamente salvo');
		$this->assertEquals('teste4', $cfg->get('param4'), 'O parametro 4 nao foi devidamente salvo');
	}
	
	

}