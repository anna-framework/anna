<?php
namespace Anna;

use Anna\Watchers\Interfaces\WatcherInterface;

/**
 * -------------------------------------------------------------
 * Watcher
 * -------------------------------------------------------------
 *
 * Classe classe abstrata fornecida para criar uma camada de verificação antes do controlador de tela ser chamado pelo sistema
 * os watchers devem ser configurados nas configurações de rotas
 *
 * @author cristianogomes
 * @since 03, Novembro 2015
 * @package Anna
 */
abstract class Watcher implements WatcherInterface 
{
    /**
     * Controller referente à chamada URL atual
     */
    protected $controller;

    public function setController($controller) 
    {
        $this->controller = $controller;
    }

}
