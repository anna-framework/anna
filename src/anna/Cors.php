<?php

namespace Anna;

/**
 * -------------------------------------------------------------
 * Cors
 * -------------------------------------------------------------.
 *
 * Classe responsável por configurar requisições Cross-domain pelo servidor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 31, Maio 2016
 */
class Cors
{
    private $config;

    public function __construct()
    {
        $config = Config::getInstance();
        $this->config = $config->get('app.cors');
    }

    public function make()
    {
        $response = null;
        if ($this->config['is-active']) {
            $origin = implode(', ', $this->config['origins']);
            $methods = implode(', ', $this->config['methods']);
            $headers = implode(', ', $this->config['headers']);

            $header = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => $methods,
                'Access-Control-Max-Age' => $this->config['max-age'],
                'Access-Control-Allow-Headers' => $headers
            ];

            $response = $this->sendResponse($header);
        } else {
            $response = $this->sendResponse();
        }

        return $response;
    }

    /**
     * Prepara o object Response para o retorno
     *
     * @param $headers
     * @return Response
     */
    private function sendResponse($headers = null)
    {
        if (is_array($headers)) {
            $header = new Response('', 200, $headers);
        } else {
            $header = new Response('cors_not_allowed', 405);
        }
        return $header;
    }

}
