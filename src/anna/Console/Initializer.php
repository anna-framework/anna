<?php

namespace Anna\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Anna\Config;

/**
 * ------------------------------------------
 * Class Initializer
 * ------------------------------------------.
 *
 * Inicializa o sistena ANNA para uso de ferramentas por linha de commando
 */
class Initializer
{
    /**
     * Inicializa a aplicação.
     *
     * @throws \Exception
     */
    public function run()
    {
    	//apenas para inicializar
    	$conf = Config::getInstance();
        $application = new Application();

        $commands = [];

        /* Carregando os comandos internos */
        $anna_commands = __DIR__.DS.'Commands'.DS;
        $anna_commands = $this->loadAppCommands($anna_commands);

        /* Carregandos os comandos criados pelos desenvolvedores */
        $app_commands = SYS_ROOT.'App'.DS.'Console'.DS;
        $app_commands = $this->loadAppCommands($app_commands);

        /* Registra todos os comandos encontrados */
        foreach ($app_commands as $cmd) {
            $commands[] = new $cmd();
        }

        foreach ($anna_commands as $cmd) {
            $class = new \ReflectionClass($cmd);

            if (!$class->isAbstract()) {
                $commands[] = new $cmd();
            }
        }

        $application->addCommands($commands);
        $application->run();
    }

    /**
     * Carrega os comandos criandos pelos desenvolvedores inicialização.
     *
     * @return array
     */
    public function loadAppCommands($path)
    {
        $fqcns = [];

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
            preg_match('~Command~', $item, $teste);

            return (count($teste)) ? true : false;
        });

        return $lista_final;
    }
}
