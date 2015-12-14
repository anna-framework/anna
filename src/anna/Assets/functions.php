<?php

use \Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator as RecursiveDir;

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
 * Extrai o nome da pasta a partir do possível namespace recebido.
 *
 * @param string $base_folder
 */
function nameToFolderName($name, $base_folder)
{
    $name = str_replace('/', '_', $name);
    $name = str_replace('\\', '_', $name);
    $parts = explode('_', $name);

    $base_path = SYS_ROOT.'App'.DS.$base_folder;
    $folder_name = '';

    foreach ($parts as $subfolder) {
        $folder_name .= DS.$subfolder;
    }

    if (!is_dir($base_path.$folder_name)) {
        return (mkdir($base_path.$folder_name)) ? $folder_name : false;
    } else {
        return $folder_name;
    }
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

    $all_files = new \RecursiveIteratorIterator(new RecursiveDir($path, RecursiveDir::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
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
                $index += 2; // Pula namespace e espaços em branco
                while (isset($tokens[$index]) && is_array($tokens[$index])) {
                    $namespace .= $tokens[$index++][1];
                }
            }

            if (T_CLASS === $tokens[$index][0]) {
                $index += 2; // Pula palavra chave 'class' e espaços em branco
                $fqcns[] = $namespace.'\\'.$tokens[$index][1];
            }
        }
    }

    $lista_final = array_filter($fqcns, function ($item) {
        preg_match('~Model~', $item, $teste);

        return (count($teste)) ? true : false;
    });

    return $lista_final;
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
    $view = Anna\View::getInstance();
    $view->setView($template);

    return $view;
}

/**
 * Registra mensagem codificada em json na view com o nome de 'sys_message'.
 *
 * @param string $message
 *                        string contento a mensagem a ser exibida para o usuário de tela
 * @param string $type
 *                        define a maneira como a mensagem será exibida, e varia de acordo
 *                        com o gosto do desenvolvedor o padrão é 'success'
 * @param mixed  $content
 *                        conteúdo opcional a ser enviado para ser processado pelo view engine
 *                        ou javascript, caso array ou objeto será convertido para json
 */
function message($message, $type = 'success', $content = null)
{
    if (is_array($content) || is_object($content)) {
        $content = json_encode($content);
    }

    $message = json_encode([
        'message' => $message,
        'type'    => $type,
        'content' => $content,
    ]);
    View::getInstance()->addParam('sys_message', $message);
}

/**
 * Retorna uma instância de response com os valores de data,
 * status e conteúdo configurado em formato json.
 *
 * @param string $message
 * @param string $content
 *
 * @return \Anna\Response
 */
function jsonMessage($message, $type = 'success', $content = null)
{
    if (is_array($content) || is_object($content)) {
        $content = json_encode($content);
    }

    $msg = json_encode([
        'message' => $message,
        'type'    => $type,
        'content' => $content,
    ]);

    return new \Anna\Response($msg, 200, [
        'content-type' => 'application/json',
    ]);
}
