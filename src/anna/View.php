<?php

namespace Anna;

use Windwalker\Renderer\BladeRenderer;

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

    public function render($view = null)
    {
        $view = $view ?: $this->template;

        return $this->renderer->render($view, $this->params);
    }

    public function setView($template)
    {
        $this->template = $template;
    }

    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function addParams($array)
    {
        foreach ($array as $name => $value) {
            $this->addParam($name, $value);
        }
    }

    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return;
    }
}
