<?php

namespace Anna;

use Windwalker\Renderer\MustacheRenderer;
use Windwalker\Renderer\BladeRenderer;
use Windwalker\Renderer\TwigRenderer;
use Anna\Routers\Router;

/**
 * -------------------------------------------------------------
 * View
 * -------------------------------------------------------------.
 *
 * Classe responsável por abstrair as funções de view, carregando os templates e seus mecanismo para a tela
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 05, Novembro 2015
 */
class View
{
    private $renderer;
    private $template;
    private $params = [];

    private static $intance;

    /**
     * Retorna a instância da classe
     */
    public static function getInstance()
    {
        if (!self::$intance) {
            self::$intance = new self();
        }

        return self::$intance;
    }

    public function __construct()
    {
        $config = Config::getInstance();
		$this->addDefaultParams();

        $view_engine = $config->get('view.view-engine');
        $view_folder = SYS_ROOT.$config->get('view.view-folder');
        $view_cache = $config->get('view.cache-folder');

        switch ($view_engine) {
            case 'blade':
                $this->renderer = new BladeRenderer([$view_folder], ['cache_path' => $view_cache]);
                break;

            case 'twig':
                $this->renderer = new TwigRenderer($view_folder);
                break;

            case 'mustache':
                $this->renderer = new MustacheRenderer($view_folder);
                break;

            default:
                $this->renderer = new BladeRenderer([$view_folder], $view_cache);
                break;
        }
    }

    /**
     * Renderiza a view especificada
     * 
     * @param string $view
     */
    public function render($view = null)
    {
        $view = $view ?: $this->template;

        return $this->renderer->render($view, $this->params);
    }

    /**
     * Configura o arquivo de view a ser renderizado
     * 
     * @param string $template
     */
    public function setView($template)
    {
        $this->template = $template;
    }

    /**
     * Adiciona um parametro na view
     * 
     * @param string $name
     * @param mixed $value
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Adiciona array de parâmetros na view, cada posição do array se tornará uma variável
     * disponível na view
     * 
     * @param array $array
     */
    public function addParams($array)
    {
        foreach ($array as $name => $value) {
            $this->addParam($name, $value);
        }
    }

    /**
     * Retorna o parâmetro especificado
     *
     * @param string $name
     * @return void|multitype:
     */
    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return;
    }
    
    /**
     * Adiciona algumas variáveis padrão na lista de parametros da view
     */
    private function addDefaultParams()
    {
		$router = Router::getInstance();
		$parameters = $router->match();
		$paths = explode('::', $parameters['path']);

		$this->addParam('_controller', $paths[0]);
		$this->addParam('_method', $paths[1]);
		$this->addParam('_route', $parameters['_route']);
    }

}
