<?php

use Anna\Application;

class ApplicationTest extends PHPUnit_Framework_TestCase{
	
	/**
	 * @cover Anna\Application
	 */
	public function testApplication(){
		$app = new Application();

		$this->assertEquals(Anna\Application::class, get_class($app), 'Nao conseguiu instaciar Application');

	}
	
	
}