<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * ---------------------------------------
 * MakeRepositoryCommand
 * ---------------------------------------.
 *
 * Classe responsável por executar o comando ANNA para criar um novo repositorio na aplicação do desenvolvedor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 10, novembro 2015
 */
class AppNameCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:name');
        $this->setDescription('Altera o namespace raiz da sua aplicacao');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome da sua aplicacao?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $root_name = Config::getInstance()->get('root-namespace');

        $path = SYS_ROOT.'App';

        $classes = $this->loadAppCommands($path);
        $trocou = false;

        foreach ($classes as $class) {
            $initname = explode('\\', $class)[0];
            $class = str_replace($initname, 'App', $class);
            $fpath = SYS_ROOT.$class.'.php';

            if (is_file($fpath)) {
                $content = file_get_contents($fpath);
                $content = str_replace($root_name.'\\', $name.'\\', $content);

                $h = fopen($fpath, 'w+');
                fwrite($h, $content);
                fclose($h);
                $trocou = true;
            }
        }

        if ($trocou) {
            $this->changeComposerRoots($name, $output);
        }

        exec('php composer dump-autoload');
        $output->writeln('Namespace raiz trocado com sucesso.');
    }

    /**
     * @param OutputInterface $output
     */
    private function changeComposerRoots($name, $output)
    {
        $file = SYS_ROOT.'composer.json';

        if (is_file($file)) {
            $content = file_get_contents($file);
            $json = json_decode($content, true);

            foreach ($json['autoload']['psr-4'] as $key => $value) {
                if ($value == 'App/') {
                    unset($json['autoload']['psr-4'][$key]);
                    $json['autoload']['psr-4'][$name] = $value;
                    break;
                }
            }

            $content = json_encode($json, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_PRETTY_PRINT);

            $h = fopen($file, 'w+');
            fwrite($h, $content);
            fclose($h);
        } else {
            $output->writeln('Arquivo composer.json nao encontrado, troque o novo namespace do autoload manualmente.');
        }
    }

    /**
     * Carrega os comandos criandos pelos desenvolvedores inicialização.
     *
     * @param string $path
     * @return array
     */
    private function loadAppCommands($path)
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

        return $fqcns;
    }
}
