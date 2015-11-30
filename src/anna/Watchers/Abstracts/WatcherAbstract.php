<?php
namespace Anna\Watchers\Abstracts;

use Anna\Watchers\Interfaces\WatcherInterface;
use Anna\Controller;

abstract class WatcherAbstract implements WatcherInterface
{
    protected $controller;
    
    public function setController(Controller $controller){
        $this->controller = $controller;
    }
}
