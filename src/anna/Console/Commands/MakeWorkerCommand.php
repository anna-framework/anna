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
 * MakeWorkerCommand
 * ---------------------------------------.
 *
 * Classe responsável por executar o comando ANNA para criar um novo comando worker
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 26, novembro 2015
 */
class MakeWorkerCommand extends Command
{
    /**
     * Efetua a configuração do comando.
     */
    protected function configure()
    {
        $this->setName('make:worker');
        $this->setDescription('Cria um novo worker');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome do seu worker?');
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
            'worker_name' => $class_name,
            'dev_name'    => Config::getInstance()->get('app.developer'),
            'data'        => date('d/m/Y'),
            'namespace'   => $root_ns.'\\Workers'.$folder_name,
        ];

        $template = TemplateHelper::getInstance()->render('worker_template', $params);
        $cmd_file_path = SYS_ROOT.'App'.DS.'Workers'.$folder_name.DS.$class_name.'Worker.php';

        if (is_file($cmd_file_path)) {
            $output->writeln('O worker '.$class_name.'Worker ja existe.');

            return true;
        }

        $hand = fopen($cmd_file_path, 'w+');
        fwrite($hand, $template);
        fclose($hand);

        $output->writeln('Worker '.$class_name.'Worker criado com sucesso.');
    }

    /**
     * Extrai o nome da pasta a partir do possível namespace recebido.
     */
    private function nameToFolderName($name)
    {
        $name = str_replace('/', '_', $name);
        $name = str_replace('\\', '_', $name);
        $parts = explode('_', $name);

        $base_path = SYS_ROOT.'App'.DS.'Workers';
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
