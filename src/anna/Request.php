<?php

namespace Anna;

/**
 * -------------------------------------------------------------
 * Request
 * -------------------------------------------------------------.
 *
 * Fachada a classe Request da biblioteca httpfoundation do symfony
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 16, Novembro 2015
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $get = filter_input_array(INPUT_GET, FILTER_DEFAULT);
        $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $cookie = filter_input_array(INPUT_COOKIE, FILTER_DEFAULT);
        $files = $this->getInputFiles();
        $server = filter_input_array(INPUT_SERVER, FILTER_DEFAULT);

    	$get = $get ? $get : [];
    	$post = $post ? $post : [];
    	$cookie = $cookie ? $cookie : [];
    	$server = $server ? $server : [];

        $this->initialize($get, $post, [], $cookie, $files, $server, null);
    }

    /**
     * Retorna parametros recebidos via get.
     *
     * @return mixed
     */
    public function get($key, $default = null, $deep = false)
    {
        return $this->query->get($key, $default, $deep);
    }

    /**
     * Retorna parametros recebidos via post.
     * 
     * @param string $param
     */
    public function post($param)
    {
        return $this->request->get($param);
    }

    private function getInputFiles()
    {
        return $_FILES;
    }
}
