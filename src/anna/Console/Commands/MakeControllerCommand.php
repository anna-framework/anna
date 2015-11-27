<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Helpers\TemplateHelper;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Routers\Router;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * ---------------------------------------
 * MakeControllerCommand
 * ---------------------------------------
 *
 * Classe responsável por executar o comando ANNA para criar um novo comando na aplicação do desenvolvedor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 09, novembro 2015
 * @package Anna\Console\Commands
 */
class MakeControllerCommand extends Command
{

    protected function configure() 
    {
        $this->setName('make:controller');
        $this->setDescription('Cria um novo controller');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome do seu controlador?');
		$this->addOption('view', null, InputOption::VALUE_NONE, 'Cria uma pasta e view para o index do novo controlador');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
		
		$name = trim($input->getArgument('name'));
		$parts = explode('\\', $name);
		$class_name = end($parts);
		$folder_name = $this->nameToFolderName($name);
		$view_folder = strtolower(str_replace(DS, '', $folder_name));
		$root_ns = Config::getInstance()->get('root-namespace');

		$params = [
			'controller_name' => nameToClassName($class_name),
			'dev_name' => Config::getInstance()->get('app.developer'),
			'data' => date('d/m/Y'),
			'namespace' => $root_ns . '\\Controllers' . $folder_name,
			'view_folder' => $view_folder ? $view_folder . '.' : ''
		];

		if ($input->getOption('view')) {
			if (!$this->generateView($class_name)) {
				$output->writeln('Anna: Não foi possível criar o arquivo de view.');
				$params['show_view'] = '';
			} else {
				$view_folder = strtolower($class_name);
				$params['show_view'] = 'return $this->show("'.$view_folder.'.index");';
			}
		} else {
			$params['show_view'] = '//do the thing';
		}

		$template = TemplateHelper::getInstance()->render('controller_template', $params);
        $cmd_file_path = SYS_ROOT . 'App' . DS . 'Controllers'  . $folder_name . DS . $class_name . 'Controller.php';

		if (is_file($cmd_file_path)) {
			$output->writeln('Anna: O Controlador ' . $class_name . 'Controller ja existe.');
			return true;
		}

        $hand = fopen($cmd_file_path, 'a+');

		if (!$hand) {
			$output->writeln('Anna: Não foi possível criar o arquivo do controlador.');
		}

        fwrite($hand, $template);
        fclose($hand);

		//add controlador na router
		if (!$this->addControllerToRoute($view_folder, $class_name)) {
			$output->writeln('Anna: Nao foi possivel criar uma nova rota.');
		}

        $output->writeln('Anna: Controlador ' . $class_name . 'Controller criado com sucesso.');
    }

	/**
	 * Extrai o nome da pasta a partir do possível namespace recebido
	 */
	private function nameToFolderName($name)
    {
		$name = str_replace('/', '_', $name);
		$name = str_replace('\\', '_', $name);
		$parts = explode('_', $name);

		$base_path = SYS_ROOT . 'App' . DS . 'Controllers';
		$controller_name = array_pop($parts);
		$folder_name = '';

		foreach ($parts as $subfolder) {
			$folder_name .= DS . $subfolder;
		}

		if (!is_dir($base_path . $folder_name)) {
			return (mkdir($base_path . $folder_name)) ? $folder_name : false;
		} else {
			return $folder_name;
		}
	}

	/**
	 * Caso o comando receba a opção -view este método irá gerar a view index e suas possíveis subpastas
	 */
	private function generateView($class_name) 
    {
		$view_folder = strtolower($class_name);
		$filename = (Config::getInstance()->get('view.view-engine') == 'blade') ? 'index.blade.php' : 'index.php';

		$path = SYS_ROOT . 'App' . DS . 'Views' . DS . $view_folder;

		if (!is_dir($path)) {
			if(!mkdir($path))
				return false;
		}

		$path .= DS . $filename;
		$hand = fopen($path, 'a+');

		if (!$hand) {
			return false;
        }

		fwrite($hand, "<div>Olá mundo!</div>");
		fclose($hand);

		return true;
	}

	private function addControllerToRoute($view_folder, $class_name)
    {
		$url_name = strtolower($class_name);

		$cfg_router_file = SYS_ROOT . 'App' . DS . 'Config' . DS . 'routes.php';
		$file = fopen($cfg_router_file, 'a+');

		if (!$file) {
			return false;
        }

		$new_route = '$router->add("/'.$url_name.'", "'.$class_name.'Controller::index");';

		fwrite($file, EOL);
		fwrite($file, $new_route . EOL);
		fclose($file);

		return true;
	}

}
