<?php

use Anna\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('SYS_ROOT', dirname(dirname(__DIR__)).DS);
        define('PS', PATH_SEPARATOR);
        define('EOL', PHP_EOL);
        define('ANNA_ROOT', __DIR__.DS);

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
    }
}
