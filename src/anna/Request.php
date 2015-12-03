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
     *
     * @param array           $query      The GET parameters
     * @param array           $request    The POST parameters
     * @param array           $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array           $cookies    The COOKIE parameters
     * @param array           $files      The FILES parameters
     * @param array           $server     The SERVER parameters
     * @param string|resource $content    The raw body data
     */
    public function __construct()
    {
        //array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null
        $this->initialize($_GET, $_REQUEST, [], $_COOKIE, $_FILES, $_SERVER, null);
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
}
