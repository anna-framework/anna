<?php

namespace Anna;

/**
 * Controller.
 *
 * Classe abstrata para herança dos controladores de tela, ela oferece ferramentas de registro de parametros e
 * envio para a view
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 05, novembro 2015
 */
abstract class Controller
{
    /**
     * @var View
     */
    protected $view;

    protected $params;

    public function init()
    {
        $this->view = View::getInstance();
    }

    /**
     * Atalho para chamar o view passando o template como parametro.
     *
     * @param string $view
     *
     * @return View
     */
    protected function view($view)
    {
        $this->view->setView($view);

        return $this->view;
    }
}
