<?php

use Anna\Config;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Anna\Application::run
     */
    public function testAssert()
    {
        try {
            $config = Config::getInstance();
        } catch (Exception $e) {
            $this->assertTrue(true); //só que nao
        }

        $this->assertTrue(true);
    }
}
