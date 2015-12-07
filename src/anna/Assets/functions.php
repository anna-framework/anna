<?php

use Anna\Response;
use Anna\View;

/**
 * Método utilizado internamente para exibição de excessões não capturadas durante o desenvolvimento.
 *
 * @param Exception $e
 */
function uncaughtExceptionHandler($e)
{
    $trace = str_replace('#', '<br /><br />', $e->getTraceAsString());

    $html = "<html>
				<head>
					<title>F4M: Deu ruim</title>
					<link href='https://fonts.googleapis.com/css?family=Roboto+Slab' rel='stylesheet' type='text/css' />
					<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
					<meta charset='utf-8' content='text/html' />
				</head>
				<body style=\"font-family: 'Source Sans Pro', serif; font-weight: 600\">
					<h1 style=\"font-family: 'Roboto Slab', serif;
								width: 800px;
								text-align: center;
								margin: auto;
								background-color: #FFF5F6;
								border-radius: 5px;
								border: 1px solid #CACACA;
								padding: 10px 0 10px 0;
								color: #8C1C1C;\">Parece que houve um erro:</h1>
					<div style='width: 800px;
								margin: 0 auto;
								background-color: #EFEFEF;
								padding: 10px;
								margin-top: 21px;
								border-radius: 5px;
								border: 1px solid #C7C7C7;'>
						<h3>{$e->getMessage()}</h3>
						<p><b>{$e->getFile()}</b>, na linha: <b>{$e->getLine()}</b></p>
						<p>{$trace}</p>
					</div>
				</body>
			</html>";
    $response = new Response($html, 200, ['chaset' => 'utf-8']);
    $response->send();
}

/**
 * Gera uma url completa com base nos parametros recebidos.
 *
 * @param string $string
 *
 * @return string
 */
function path($string)
{
    $url = \Anna\Config::getInstance()->get('app.url').$string;

    return $url;
}

/**
 * Invoca um helper.
 *
 * @param $helper
 *
 * @return mixed
 */
function helper($helper)
{
    $helper = ucfirst($helper).'Helper';
    $helper = implode('\\', ['App', 'Helpers']).'\\'.$helper;

    return new $helper();
}

/**
 * Monta nome completo de uma classe a partir do nome da mesma e um array contendo os podaços que compõe o namespace.
 *
 * @example Para montar o nome completo App\Controllers\HomeController:
 * mountCtrlFullname('HomeController', ['App', 'Controllers']);
 *
 * @param string $ctrl
 * @param array  $array
 *
 * @return string
 */
function mountCtrlFullName($ctrl, $array)
{
    return '\\'.implode('\\', $array).'\\'.$ctrl;
}

/**
 * Converte o nome informado pelo desenvolvedor para um nome padronizado para classes.
 *
 * @param string $name
 *
 * @return string
 */
function nameToClassName($name)
{
    $name = str_replace('-', '_', $name);
    $name = str_replace('.', '_', $name);
    $name = str_replace(':', '_', $name);

    $part_names = explode('_', $name);
    $class_name = '';

    foreach ($part_names as $pn) {
        $pn = strtolower($pn);
        $class_name .= ucfirst($pn);
    }

    return $class_name;
}

/**
 * Extrai o nome da pasta a partir do possível namespace recebido
 * @param string $base_folder
 */
function nameToFolderName($name, $base_folder){
    $name = str_replace('/', '_', $name);
    $name = str_replace('\\', '_', $name);
    $parts = explode('_', $name);

    $base_path = SYS_ROOT . 'App' . DS . $base_folder;
    $controller_name = array_pop($parts);
    $folder_name = '';

    foreach($parts as $subfolder){
        $folder_name .= DS . $subfolder;
    }

    if(!is_dir($base_path . $folder_name)){
        return (mkdir($base_path . $folder_name)) ? $folder_name : false;
    }else{
        return $folder_name;
    }
}

function bcrypt($string)
{
    $custo = 8;
    $salt = 'fbhaeliflaefhb2387r237';
    $hash = crypt($string, '$2a$'.$custo.'$'.$salt.'$');

    return $hash;
}

/**
 * Atalho para retornar a view com o template configurado.
 *
 * @return View
 */
function view($template)
{
    $view = View::getInstance();
    $view->setView($template);

    return $view;
}

