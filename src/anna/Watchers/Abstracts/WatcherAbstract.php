<?php

namespace Anna\Watchers\Abstracts;

use Anna\Controller;
use Anna\Watchers\Interfaces\WatcherInterface;

/**
 * -------------------------------------------------------------
 * WatcherAbstract
 * -------------------------------------------------------------.
 *
 * Watchers são vigias que protegerão rotas verificando tudo que for necessário
 * antes da invocação do controller pelo sistema, esta classe abstrai algumas funcionalidades
 * deixando para o desenvolvedor apenas a lógica das verificações que ele deseja realizar
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 10, dezembro 2015
 */
abstract class WatcherAbstract implements WatcherInterface
{
    protected $controller;

    private $url_params;

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function setUrlParams($params)
    {
        $this->url_params = $params;
    }

    protected function getParam($param_name)
    {
        if (isset($this->url_params[$param_name])) {
            return $this->url_params[$param_name];
        }

        return;
    }

    protected function getParams()
    {
        return $this->url_params;
    }
}
