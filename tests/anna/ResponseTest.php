<?php

use Anna\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
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
