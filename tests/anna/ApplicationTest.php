<?php

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Anna\Application::run
     */
    public function testAssert()
    {
        $app = new Anna\Application();
        try {
            $app->run();
        } catch (Exception $e) {
            $this->fail('deu pau');
        }

        $this->assertTrue(true);
    }
}
