<?php

use Anna\View;
use Anna\Config;

class ViewTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		define('DS', DIRECTORY_SEPARATOR);

		define('SYS_ROOT', dirname(dirname(__DIR__)).DS);

		chdir(SYS_ROOT);

		if (!defined('PS')) {
			define('PS', PATH_SEPARATOR);
		}
		
		if (!defined('EOL')) {
			define('EOL', PHP_EOL);
		}
		
		if (!defined('ANNA_ROOT')) {
			define('ANNA_ROOT', __DIR__.DS);
		}
	}

	/**
	 * @cover \Anna\View
	 */
	public function testView(){
		try {
			$view = new View();
		} catch (Exception $e){
			$this->assertTrue(false, $e->getMessage());
		}

		//instance test
		$this->assertEquals(View::class, get_class($view));

		//addParam test
		$error = false;
		try {
			$view->addParam('testeParam', 'testeValue');
		} catch (Exception $e){
			$error = true;
		}

		$this->assertFalse($error, 'Falha na hora de adicionar parametro ao view');

		try {
			$teste = $view->getParam('testeParam');
		} catch (Exception $e){
			$teste = false;
		}

		$this->assertEquals('testeValue', $teste, 'Falha na recuperacao de parametro da view');

	}

}