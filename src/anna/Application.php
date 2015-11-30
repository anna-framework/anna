<?php
namespace Anna;

use DI\ContainerBuilder;
use DI\Definition\Source\DefinitionArray;
use Anna\Routers\Router;
use Anna\View;
use Anna\Response;
use Anna\Config;

 /**
  * -------------------------------------------------------------
  * Application
  * -------------------------------------------------------------
  *
  * Classe principal do Anna, coordena os eventos e os processos
  *
  * @author Cristiano Gomes <cmgomes.es@gmail.com>
  * @since 03, Novembro 2015
  * @package Anna
  */
class Application 
{

	private $app_root_namespace;

	/**
	 * Container responsável pela Dependence Injection
	 *
	 * @var DI\Container
	 */
	private $di;

	private static $instance;

	/**
	 * Retorna singleton da classe Application
	 * @return Application
	 */
	public static function getInstance()
    {
		if (!self::$instance) {
			self::$instance = new Application();
        }

		return self::$instance;
	}

	public function run()
    {
		$this->config();
		$this->app_root_namespace = Config::getInstance()->get('root-namespace');

		$url_params = $this->doRoute();
        if (!$url_params) {
            return;
        }

		$controller = $this->wakeUpController($url_params);

		if (isset($url_params['watcher'])){
			$watcher_result = $this->runWatcher($url_params['watcher'], $controller);
        }

        if(!$watcher_result){
            $response = new Response('acesso_negado', 404);
            $response->display();
            return;
        }

		$method = $url_params['method'];

		if (isset($url_params['method_params'])){
			$result = call_user_func_array([$controller, $method], $url_params['method_params']);
		} else {
			$result = call_user_func([$controller, $method]);
        }
        
		$this->processResult($result);
	}

	/**
	 * Retorna o container do Dependence Injector
	 *
	 * @return DI\Container
	 */
	public function getInjector()
    {
		return $this->di;
	}

	/**
	 * Processa o resultado recebido do controller e envia para a home
	 * 
	 * @return mixed
	 */
	private function processResult($result)
    {
		if (is_object($result) || is_array($result)) {
			if ($result instanceof View) {
				$response = new Response( $result->render());
				$response->display();
			} elseif($result instanceof Response) {
				$result->display();
			} elseif(is_array($result)) {
				$response = new Response('');
				$response->displayJson($result);
			}
		} else {
			echo $result;
		}
	}

	/**
	 * Instancia o controller e prepara-o para ser executado
	 *
	 * @param array $url_params
	 */
	private function wakeUpController($url_params)
    {
		$controller = $url_params['controller'];
        //die($this->app_root_namespace);
		$ctrl_full_name = mountCtrlFullName($controller, [$this->app_root_namespace, 'Controllers']);

		$controller = $this->di->get($ctrl_full_name);
		$controller->init();

		return $controller;
	}

	/**
	 * Executa o watcher determinado para a rota encontrada
	 *
	 * @param string $watcher_name
	 */
	private function runWatcher($watcher_name, $controller)
    {
		$watcher_name = ucfirst($watcher_name) . 'Watcher';

		$full_name_watcher = mountCtrlFullName($watcher_name, [$this->app_root_namespace, 'Watchers']);
		$watcher = new $full_name_watcher();

		$watcher->setController($controller);
		return $watcher->run();
	}

	/**
	 * Efetua a busca da rota compatível com a url recebida e préprocessa o resultado para utilização
	 * posterior na classe
	 *
	 * @return array
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
		foreach($parameters as $k => $v){
			if (!in_array($k, ['path', '_route', 'watcher'])) {
				$method_params[$k] = $v;
			}
		}

		$parameters['method_params'] = $method_params;

		list($controller, $method) = explode('::', $parameters['path']);
		$parameters['controller'] = $controller;
		$parameters['method'] = $method;

		return $parameters;
	}

	/**
	 * Efetua algumas configurações avançadas do sistema
	 */
	private function config()
    {
		Config::getInstance()->set('ANNA_PATH', __DIR__ . DS);

		set_exception_handler('uncaughtExceptionHandler');

		//Implementando o Dependence Injection automatizado
		$di_builder = new ContainerBuilder();
		$di_builder->useAnnotations(true);

		$this->di = $di_builder->build();
	}
}
