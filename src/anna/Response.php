<?php

namespace Anna;

use Anna\Helpers\JsonHelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * -------------------------------------------------------------
 * Response
 * -------------------------------------------------------------.
 *
 * Fachada a classe Response da biblioteca httpfoundation do symfony
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 16, Novembro 2015
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{
    /**
     * Exibe o conteúdo para o solicitante
     */
    public function display()
    {
        $di = Application::getInstance()->getInjector();
        $request = $di->get('Anna\Request');
        $this->prepare($request);
        $this->send();
    }

    /**
     * Exibe o conteúdo em formato json
     *
     * @param $data
     */
    public function displayJson($data)
    {
        if (is_array($data) || is_object($data)) {
            $this->content = JsonHelper::encode($data);
        }

        $this->addHeaders(['Content-type' => 'application/json']);
        $this->display();
    }

    /**
     * Adiciona headers
     *
     * @param $headers
     */
    public function addHeaders($headers) {
        if (!$this->headers instanceof ResponseHeaderBag) {
            $this->headers = new ResponseHeaderBag();
        }

        $this->headers->add($headers);
    }
}
