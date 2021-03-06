<?php

namespace Anna;

use Anna\Routers\Router;
use DI\ContainerBuilder;

/**
 * -------------------------------------------------------------
 * Application
 * -------------------------------------------------------------.
 *
 * Classe principal do Anna, coordena os eventos e os processos
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 03, Novembro 2015
 */
class Application
{
    const WATCHER = 'watcher';

    const METHOD_PARAMS = 'method_params';

    private $app_root_namespace;

    /**
     * Container responsável pela Dependence Injection.
     *
     * @var \DI\Container
     */
    private $di;

    private static $instance;

    /**
     * Retorna singleton da classe Application.
     *
     * @return Application
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function run()
    {
        $this->config();
        $this->app_root_namespace = Config::getInstance()->get('root-namespace');

        $cors = $this->configureCors();
        if ($cors instanceof Response) {
            $cors->display();

            return;
        }

        $url_params = $this->doRoute();
        if (!$url_params) {
            return;
        }

        $controller = $this->wakeUpController($url_params);

        if (isset($url_params[static::WATCHER])) {
            $watcher_result = $this->runWatcher($url_params[static::WATCHER], $controller, $url_params['method_params']);

            if ($watcher_result == false) {
                $response = new Response('acesso_negado', 401);
                $response->display();

                return;
            }
        }

        $method = $url_params['method'];

        if (isset($url_params[static::METHOD_PARAMS])) {
            $result = call_user_func_array([
                $controller,
                $method,
            ], $url_params[static::METHOD_PARAMS]);
        } else {
            $result = call_user_func([
                $controller,
                $method,
            ]);
        }

        $this->processResult($result);
    }

    /**
     * Retorna o container do Dependence Injector.
     *
     * @return \DI\Container
     */
    public function getInjector()
    {
        if (!$this->di) {
            $this->config();
        }

        return $this->di;
    }

    /**
     * Processa o resultado recebido do controller e envia para a home.
     *
     * @param $result
     *
     * @return void
     */
    private function processResult($result)
    {
        if (is_object($result) || is_array($result)) {
            if ($result instanceof View) {
                $response = $this->addCorsHeaders(new Response($result->render()));
                $response->display();
            } elseif ($result instanceof Response) {
                $this->addCorsHeaders($result)->display();
            } else {
                $response = $this->addCorsHeaders(new Response(''));
                $response->displayJson($result);
            }
        } else {
            echo $result;
        }
    }

    /**
     * Instancia o controller e prepara-o para ser executado.
     *
     * @param array $url_params
     *
     * @return mixed
     */
    private function wakeUpController($url_params)
    {
        $controller = $url_params['controller'];
        $ctrl_full_name = mountCtrlFullName($controller, [
            $this->app_root_namespace,
            'Controllers',
        ]);

        $controller = $this->di->get($ctrl_full_name);
        $controller->init();

        return $controller;
    }

    /**
     * Executa o watcher determinado para a rota encontrada.
     *
     * @param string $watcher_name
     * @param $controller
     * @param array $params
     *
     * @return
     */
    private function runWatcher($watcher_name, $controller, $params = [])
    {
        $watcher_name = ucfirst($watcher_name).'Watcher';

        $full_name_watcher = mountCtrlFullName($watcher_name, [
            $this->app_root_namespace,
            'Watchers',
        ]);
        $watcher = $this->di->get($full_name_watcher);

        $watcher->setController($controller);
        $watcher->setUrlParams($params);

        return $watcher->run();
    }

    /**
     * Efetua a busca da rota compatível com a url recebida e préprocessa o resultado para utilização
     * posterior na classe.
     *
     * @return mixed
     */
    private function doRoute()
    {
        $router = Router::getInstance();
        $parameters = $router->match();

        if ($parameters instanceof Response) {
            $parameters->display();

            return false;
        }

        $method_params = [];
        foreach ($parameters as $k => $v) {
            if (!in_array($k, [
                'path',
                '_route',
                static::WATCHER,
            ])) {
                $method_params[$k] = $v;
            }
        }

        $parameters[static::METHOD_PARAMS] = $method_params;

        $explode = explode('::', $parameters['path']);
        $controller = $explode[0];
        $method = (isset($explode['1']) && $explode[1] != '') ? $explode[1] : 'index';

        $parameters['controller'] = $controller;
        $parameters['method'] = $method;

        return $parameters;
    }

    /**
     * Efetua algumas configurações avançadas do sistema.
     */
    private function config()
    {
        Config::getInstance()->set('ANNA_PATH', __DIR__.DS);

        if (is_callable('uncaughtExceptionHandler')) {
            set_exception_handler('uncaughtExceptionHandler');
        }

        // Implementando o Dependence Injection automatizado
        $di_builder = new ContainerBuilder();
        $di_builder->useAnnotations(true);

        $this->di = $di_builder->build();
    }

    /**
     * Configura acesso para chamadas cross-domain.
     */
    private function configureCors()
    {
        $request = new Request();
        $response = null;
        if ($request->getMethod() == 'OPTIONS') {
            $cors = new Cors();
            $response = $cors->make();
        }

        return $response;
    }

    /**
     * Se necessário adiciona cabeçalhos de cors para requisições externas posteriormente ao processamento.
     *
     * @param Response $response
     *
     * @return Response
     */
    private function addCorsHeaders(Response $response)
    {
        $request = new Request();
        $corsHeaders = [];

        if ($request->headers->get('Origin') != $request->getHost()) {
            $cors = new Cors();
            $corsHeaders = $cors->genCorsHeaders();
        }

        if (count($corsHeaders) > 0) {
            $response->addHeaders($corsHeaders);
        }

        return $response;
    }
}
