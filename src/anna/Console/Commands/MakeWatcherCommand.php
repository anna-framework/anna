<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Console\Helpers\TemplateHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * MakeModelCommand
 * ---------------------------------------.
 *
 * Classe responsável por executar o comando ANNA para criar um novo comando na aplicação do desenvolvedor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 09, novembro 2015
 */
class MakeWatcherCommand extends Command
{
    /**
     * Efetua a configuração do comando.
     */
    protected function configure()
    {
        $this->setName('make:watcher');
        $this->setDescription('Cria um novo watcher');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome do seu watcher?');
    }

    /**
     * Executa o comando.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $parts = explode('\\', $name);
        $class_name = end($parts);
        $folder_name = $this->nameToFolderName($name);
        $root_ns = Config::getInstance()->get('root-namespace');

        $params = [
            'watcher_name' => $class_name,
            'dev_name'     => Config::getInstance()->get('app.developer'),
            'data'         => date('d/m/Y'),
            'namespace'    => $root_ns.'\\Watchers'.$folder_name,
        ];

        $template = TemplateHelper::getInstance()->render('watcher_template', $params);
        $cmd_file_path = SYS_ROOT.'App'.DS.'Watchers'.$folder_name.DS.$class_name.'Watcher.php';

        if (is_file($cmd_file_path)) {
            $output->writeln('O watcher '.$class_name.'Watcher ja existe.');

            return true;
        }

        $hand = fopen($cmd_file_path, 'w+');
        fwrite($hand, $template);
        fclose($hand);

        $output->writeln('Watcher '.$class_name.'Watcher criado com sucesso.');
    }

    /**
     * Extrai o nome da pasta a partir do possível namespace recebido.
     */
    private function nameToFolderName($name)
    {
        $name = str_replace('/', '_', $name);
        $name = str_replace('\\', '_', $name);
        $parts = explode('_', $name);

        $base_path = SYS_ROOT.'App'.DS.'Watchers';
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
}
