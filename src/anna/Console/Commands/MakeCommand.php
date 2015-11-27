<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;

use \Anna\Console\Helpers\TemplateHelper;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * MakeCommand
 * ---------------------------------------
 *
 * Classe responsável por executar o comando ANNA para criar um novo comando na aplicação do desenvolvedor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 04, novembro 2015
 * @package Anna\Console\Commands
 */
class MakeCommand extends Command
{

    protected function configure() 
    {
        $this->setName('make:command');
        $this->setDescription('Cria um novo comando');

        //add argumentos
        $this->addArgument('name', InputArgument::REQUIRED, 'Informe um nome para seu comando');
        $this->addArgument('description', InputArgument::OPTIONAL, 'Uma descricao opcional.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $cmd_name = $input->getArgument('name');
        $cmd_description = ($input->hasArgument('description')) ? $input->getArgument('description') : '';
        $class_name = $this->nameToClassName($cmd_name);
        $dev_name = Config::getInstance()->get('app.developer');
        $data = date('d/m/Y');
        $root_ns = Config::getInstance()->get('root-namespace');

        $params = [
        		'command_class_name' => $class_name,
        		'dev_name' => $dev_name,
        		'data_atual' => $data,
        		'command_name' => $cmd_name,
        		'command_description' => $cmd_description,
        		'root_ns' => $root_ns
        ];

        $template = TemplateHelper::getInstance()->render('command_template', $params);
        $cmd_file_path = SYS_ROOT . 'App' . DS . 'Console' . DS . $class_name . 'Command.php';

        $hand = fopen($cmd_file_path, 'a+');
        fwrite($hand, $template);
        fclose($hand);

        $output->writeln('Comando ' . $class_name . 'Command criado com sucesso.');
    }

    /**
     * Converte o nome do comando informado pelo desenvolvedor para um nome padronizado para classes
     *
     * @param string $command_name
     * @return string
     */
    private function nameToClassName($command_name)
    {
        $command_name = str_replace('-', '_', $command_name);
        $command_name = str_replace('.', '_', $command_name);
        $command_name = str_replace(':', '_', $command_name);

        $part_names = explode('_', $command_name);
        $class_name = '';

        foreach ($part_names as $pn) {
            $pn = strtolower($pn);
            $class_name .= ucfirst($pn);
        }

        return $class_name;
    }


}
