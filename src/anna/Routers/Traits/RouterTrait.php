<?php

namespace Anna\Routers\Traits;

use Anna\Config;
use Anna\Error;
use Anna\Routers\SubRouter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * -------------------------------------------------------------
 * RouterTrait
 * -------------------------------------------------------------.
 *
 * Trait utilizada tanto nos Router quanto no SubRouter
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 13, Novembro 2015
 */
trait RouterTrait
{
    /**
     * Coleção de rotas a serem examinadas.
     *
     * @var RouteCollection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new RouteCollection();
        $this->collection->setHost(Config::getInstance()->get('app.url'));

        $this->addDefaultsWildCards();
    }

    /**
     * Adiciona uma nova rota à coleção de rotas do sistema.
     *
     * Os parametros podem ser somente uma string contendo nome do controlador e método da rota no padrão
     * controller::method ou pode ser um array cujo a primeira posição seja o controlador e método enquanto as demais
     * posições, todas opcionais, podem ser arrays cujo as chaves seriam:
     *
     * - requirements
     * - options
     * - hosts
     * - schemes
     * - methods
     *
     * @see https://symfony.com/doc/current/components/routing/introduction.html
     *
     * @param string $route_name
     * @param mixed  $parameters
     */
    public function add($route_name, $path, $parameters = [])
    {
        if (count($parameters) > 0) {
            $requirements = isset($parameters['mask']) ? $parameters['mask'] : [];
            $options = isset($parameters['options']) ? $parameters['options'] : [];
            $hosts = isset($parameters['host']) ? $parameters['host'] : null;
            $schemes = isset($parameters['schemes']) ? $parameters['schemes'] : [];
            $methods = isset($parameters['methods']) ? $parameters['methods'] : [];
            $route = new Route($route_name, ['path' => $path], $requirements, $options, $hosts, $schemes, $methods);
        } else {
            $route = new Route($route_name, ['path' => $path]);
        }

        $this->collection->add($route_name, $route);
    }

    public function post($route_name, $path, $parameters = [])
    {
        $this->addEspecifcMethod($route_name, $path, 'POST', $parameters);
    }

    public function get($route_name, $path, $parameters = [])
    {
        $this->addEspecifcMethod($route_name, $path, 'GET', $parameters);
    }

    public function put($route_name, $path, $parameters = [])
    {
        $this->addEspecifcMethod($route_name, $path, 'PUT', $parameters);
    }

    public function delete($route_name, $path, $parameters = [])
    {
        $this->addEspecifcMethod($route_name, $path, 'DELETE', $parameters);
    }

    /**
     * @param string $method
     */
    private function addEspecifcMethod($route_name, $path, $method, $parameters = [])
    {
        if (isset($parameters['methods']) && is_array($parameters['methods'])) {
            array_push($parameters['methods'], $method);
        } else {
            $parameters['methods'] = [$method];
        }

        $this->add($route_name, $path, $parameters);
    }

    /**
     * Adiciona um prefixo às url's das rotas que são registradas através do método anônimo $config.
     *
     * @param string   $prefix_name
     * @param callable $config
     */
    public function addPrefix($prefix_name, $config)
    {
        $router = $this->getSubRouter();

        if (is_callable($config)) {
            $config($router);
        } else {
            Error::log(new \Exception('O segundo parâmetro deve ser um callable'));
            return;
        }

        $sub_collection = $router->getCollection();
        $sub_collection->addPrefix($prefix_name);

        unset($router);

        $this->collection->addCollection($sub_collection);
    }

    /**
     * Adiciona um watcher às rotas registradas pelo método anônimo config.
     *
     * @param string   $watcher_name
     * @param callable $config
     */
    public function addWatcher($watcher_name, $config)
    {
        $router = $this->getSubRouter();

        if (is_callable($config)) {
            $config($router);
        } else {
            $e = new Error();
            $e->log(new \Exception('Segundo parâmetro não é uma função do tipo callable'));
        }

        $sub_collection = $router->getCollection();
        $sub_collection->addDefaults(['watcher' => $watcher_name]);

        $this->collection->addCollection($sub_collection);
    }

    public function addGroup($config, $callback)
    {
        $router = $this->getSubRouter();

        if (is_callable($callback)) {
            $callback($router);
        } else {
            $e = new Error();
            $e->log(new \Exception('Segundo parâmetro não é uma função do tipo callable'));
        }

        $sub_collection = $router->getCollection();

        if (isset($config['defaults'])) {
            $sub_collection->addDefaults($config['defaults']);
        }

        if (isset($config['mask'])) {
            $sub_collection->addRequirements($config['mask']);
        }

        if (isset($config['options'])) {
            $sub_collection->addOptions($config['options']);
        }

        if (isset($config['host'])) {
            $sub_collection->setHost($config['host']);
        }

        if (isset($config['schemes'])) {
            $sub_collection->setSchemes($config['schemes']);
        }

        if (isset($config['methods'])) {
            $sub_collection->setMethods($config['methods']);
        }

        $this->collection->addCollection($sub_collection);
    }

    public function addResource($route_name, $resource, $options = null)
    {
        $except = [];
        if (is_array($options) && isset($options['except']) && is_array($options['except'])) {
            $except = $options['except'];
        }

        if (!in_array('read', $except)) {
            $route_read = new Route($route_name, ['path' => $resource.'::read'], [], [], '', [], ['GET']);
            $route_read_any = new Route($route_name.'/{any}', ['path' => $resource.'::read'], [], [], '', [], ['GET']);

            $this->collection->add($route_name.'read', $route_read);
            $this->collection->add($route_name.'read_any', $route_read_any);
        }

        if (!in_array('create', $except)) {
            $route_create_any = new Route($route_name.'/{any}', ['path' => $resource.'::create'], [], [], '', [], ['POST']);
            $this->collection->add($route_name.'create_any', $route_create_any);
        }

        if (!in_array('update', $except)) {
            $route_update_any = new Route($route_name.'/{any}', ['path' => $resource.'::update'], [], [], '', [], ['PUT']);
            $this->collection->add($route_name.'update_any', $route_update_any);
        }

        if (!in_array('delete', $except)) {
            $route_delete_any = new Route($route_name.'/{any}', ['path' => $resource.'::delete'], [], [], '', [], ['DELETE']);
            $this->collection->add($route_name.'delete_any', $route_delete_any);
        }
    }

    /**
     * Método deve ser implementado nas classes que utilizam essa trait
     */
    abstract public function getSubRouter();
    
    /**
     * Adiciona wildcards padrões para serem utilizados na detecção de rotas por regex.
     */
    private function addDefaultsWildCards()
    {
        $this->collection->addRequirements(['any' => '[a-zA-Z\-\.0-9]+\=\?']);
    }
}
