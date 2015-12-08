<?php

use Anna\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!defined('DS')) {
    		define('DS', DIRECTORY_SEPARATOR);
    	}

    	if (!defined('SYS_ROOT')) {
    		define('SYS_ROOT', dirname(dirname(__DIR__)).DS);
    	}

    	if (!defined('PS')) {
    		define('PS', PATH_SEPARATOR);
    	}

    	if (!defined('EOL')) {
    		define('EOL', PHP_EOL);
    	}

    	if (!defined('ANNA_ROOT')) {
    		define('ANNA_ROOT', __DIR__.DS);
    	}

        chdir(SYS_ROOT);
    }

    /**
     * @cover \Anna\Response
     */
    public function testResponse()
    {
        try {
            $request = new Response();
        } catch (Exception $e) {
            $this->assertTrue(false, $e->getMessage());
        }

        //instance test
        $this->assertEquals(Response::class, get_class($request));
    }
}
