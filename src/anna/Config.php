<?php

namespace Anna;

/**
 * -------------------------------------------------------------
 * Config
 * -------------------------------------------------------------
 *
 * Classe centralizadora e gerenciadora das configurações presentes na pasta App/Config,
 * Esta classe utiliza patterno singleton para tornar-se disponível em todo o sistema
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 03, Novembro 2015
 * @package Anna
 */
class Config
{

	private static $instance;

	private $config_array;

	private $config_path;

	public function __construct()
    {
        if(!defined('SYS_ROOT')){
            define('SYS_ROOT', dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DS);
        }

        chdir(SYS_ROOT);

        if(!defined('DS')){
            define('DS', DIRECTORY_SEPARATOR);
        }
        
        if(!defined('PS')){
            define('PS', PATH_SEPARATOR);
        }
        
        if(!defined('EOL')){
            define('EOL', PHP_EOL);
        }

		$this->config_array = [];
		$this->config_path = SYS_ROOT . 'App' . DS . 'Config' . DS;
	}

	/**
	 * @return Config
	 */
	public static function getInstance()
    {
		if (!self::$instance) {
			self::$instance = new Config();
        }

		return self::$instance;
	}

	public function set($key, $value)
    {
		$this->config_array[$key] = $value;
	}

	public function get($keys)
    {
		$keys = explode('.', $keys);
		$tmp = $this->config_array;

		foreach($keys as $key){
			$tmp = isset($tmp[$key]) ? $tmp[$key] : null;
        }
        
		return $tmp;
	}

	public function addConfigs($array)
    {
		foreach($array as $key => $value) {
			$this->config_array[$key] = $value;
        }
	}

	public function make()
    {
		$this->loadRootNamespace();

		require_once 'Assets' . DS . 'functions.php';

		$view = include $this->config_path . 'view.php';
		$app = include $this->config_path . 'app.php';
		$database = include $this->config_path . 'database.php';
		$cache = include $this->config_path . 'cache.php';

		$this->config_array['view'] = $view;
		$this->config_array['app'] = $app;
		$this->config_array['database'] = $database;
		$this->config_array['cache'] = $cache;

		include $this->config_path . 'routes.php';
	}

	/**
	 * Procura o namespace raiz da aplicação e registra
	 */
	private function loadRootNamespace()
    {
		$string = file_get_contents('composer.json');
		$composer_info = json_decode($string, true);

		foreach($composer_info['autoload']['psr-4'] as $namespace => $path){
			if ($path == 'App/') {
				Config::getInstance()->set('root-namespace', str_replace('\\', '', $namespace));
			}
		}
	}

}
