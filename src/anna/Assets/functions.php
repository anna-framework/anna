<?php
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
    $response = new Anna\Response($html, 200, ['chaset' => 'utf-8']);
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

/**
 * Carrega os models criados pelo desenvolvedor.
 *
 * @return array
 */
function loadAppModels()
{
    $fqcns = [];
    $path = SYS_ROOT.'App'.DS.'Models'.DS;

    $all_files = new \RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
    $php_files = new \RegexIterator($all_files, '/\.php$/');

    foreach ($php_files as $php_file) {
        $content = file_get_contents($php_file->getRealPath());
        $tokens = token_get_all($content);
        $namespace = '';

        for ($index = 0; isset($tokens[$index]); $index++) {
            if (!isset($tokens[$index][0])) {
                continue;
            }

            if (T_NAMESPACE === $tokens[$index][0]) {
                $index += 2; // Pula namespace e espaà¸£à¸‡os em branco
                while (isset($tokens[$index]) && is_array($tokens[$index])) {
                    $namespace .= $tokens[$index++][1];
                }
            }

            if (T_CLASS === $tokens[$index][0]) {
                $index += 2; // Pula palavra chave 'class' e espaà¸£à¸‡os em branco
                $fqcns[] = $namespace.'\\'.$tokens[$index][1];
            }
        }
    }

    $lista_final = array_filter($fqcns, function($item) {
        preg_match('~Model~', $item, $teste);

        return (count($teste)) ? true : false;
    });

    return $lista_final;
}
