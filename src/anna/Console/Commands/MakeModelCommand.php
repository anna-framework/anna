<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Console\Helpers\TemplateHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
class MakeModelCommand extends Command
{
    /**
     * Efetua a configuração do comando.
     */
    protected function configure()
    {
        $this->setName('make:model');
        $this->setDescription('Cria um novo model');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome do seu model?');

        $this->addOption('tablename', 't', InputOption::VALUE_REQUIRED, 'Seta o nome desejado para a tabela, caso contrario sera o nome do model');
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
        $folder_name = nameToFolderName($name, 'Models');
        $root_ns = Config::getInstance()->get('root-namespace');

        if ($input->getOption('tablename')) {
            $table_name = $input->getOption('tablename');
        } else {
            $name = str_replace('\\', '', $name);

            preg_match_all('/([A-Z][a-z]+)/', $name, $teste);
            $table_name = implode('_', $teste[1]);
            $table_name = strtolower($table_name);
        }

        $tmp_name_folder = str_replace('/', '\\', $folder_name);
        $params = [
            'model_name' => $class_name,
            'dev_name'   => Config::getInstance()->get('app.developer'),
            'data'       => date('d/m/Y'),
            'namespace'  => $root_ns.'\\Models'.$tmp_name_folder,
            'table_name' => $table_name,
        ];

        $template = TemplateHelper::getInstance()->render('model_template', $params);
        $cmd_file_path = SYS_ROOT.'App'.DS.'Models'.$folder_name.DS.$class_name.'Model.php';

        if (is_file($cmd_file_path)) {
            $output->writeln('O model '.$class_name.'Model ja existe.');

            return true;
        }

        $hand = fopen($cmd_file_path, 'w+');
        fwrite($hand, $template);
        fclose($hand);

        $output->writeln('Model '.$class_name.'Model criado com sucesso.');
    }
}
