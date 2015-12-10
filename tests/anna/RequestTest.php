<?php

use Anna\Request;

class RequestTest extends PHPUnit_Framework_TestCase
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
     * @cover \Anna\Request
     */
    public function testRequest()
    {
        try {
            $request = new Request();
        } catch (Exception $e) {
            $this->assertTrue(false, $e->getMessage());
        }

        //instance test
        $this->assertEquals(Request::class, get_class($request));

        $teste = $request->get('teste');

        $this->assertEquals(null, $teste);

        $testePost = $request->post('testePost');

        $this->assertEquals(null, $testePost);
    }
}
