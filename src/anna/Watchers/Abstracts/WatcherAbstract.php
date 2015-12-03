<?php

namespace Anna\Watchers\Abstracts;

use Anna\Controller;
use Anna\Watchers\Interfaces\WatcherInterface;

abstract class WatcherAbstract implements WatcherInterface
{
    protected $controller;

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }
}
