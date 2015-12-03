<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Console\Helpers\TemplateHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
class MakeRepositoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:repository');
        $this->setDescription('Cria um novo repositorio');
        $this->addArgument('name', InputArgument::REQUIRED, 'Qual o nome do seu repositorio?');
        $this->addOption('model', 'm', InputOption::VALUE_NONE, 'Caso passado cria um model com mesmo nome do repositorio.');
        $this->addOption('abstract', 'a', InputOption::VALUE_NONE, 'Cria o respositório diretamente da classe abstrata, para utilizar com outros drivers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $parts = explode('\\', $name);
        $class_name = end($parts);
        $folder_name = nameToFolderName($name, 'Repositories');
        $root_ns = Config::getInstance()->get('root-namespace');

        $params = [
            'repository_name' => nameToClassName($class_name),
            'dev_name'        => Config::getInstance()->get('app.developer'),
            'data'            => date('d/m/Y'),
            'namespace'       => $root_ns.'\\Repositories'.$folder_name,
        ];

        if ($input->getOption('model')) {
            $model = nameToClassName($class_name);

            if (!$this->callMakeModel($model, $output)) {
                $output->writeln('Anna: Nao foi possivel criar o modelo '.$model.'Model');
                $params['construct'] = '';
                $params['use_model'] = '';
            } else {
                $model .= 'Model';
                $declaration = '/**'.EOL;
                $declaration .= "\t * @inject ".$model.EOL;
                $declaration .= "\t * @var ".$model.EOL;
                $declaration .= "\t */".EOL;
                $declaration .= "\t".'public $model;'.EOL;

                $params['construct'] = $declaration;
                $params['use_model'] = 'use '.$root_ns.'\\Models\\'.$model.';';
            }
        } else {
            $params['construct'] = '';
            $params['use_model'] = '';
        }

        if ($input->getOption('abstract')) {
            $params['use_suberclass'] = 'use Anna\Repositories\Abstracts\Repository;';
        } else {
            $params['use_suberclass'] = 'use Anna\Repositories\Repository;';
        }

        $template = TemplateHelper::getInstance()->render('repository_template', $params);
        $cmd_file_path = SYS_ROOT.'App'.DS.'Repositories'.$folder_name.DS.$class_name.'Repository.php';

        if (is_file($cmd_file_path)) {
            $output->writeln('O Repositorio '.$class_name.'Repository ja existe.');

            return true;
        }

        $hand = fopen($cmd_file_path, 'a+');
        fwrite($hand, $template);
        fclose($hand);

        $output->writeln('Anna: Repositorio '.$class_name.'Repository criado com sucesso.');
    }

    /**
     * Caso o comando receba a opção -view este método irá gerar a view index e suas possíveis subpastas.
     */
    private function generateView($class_name)
    {
        $view_folder = strtolower($class_name);
        $filename = (Config::getInstance()->get('view.view-engine') == 'blade') ? 'index.blade.php' : 'index.php';

        $path = SYS_ROOT.'App'.DS.'Views'.DS.$view_folder;

        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return false;
            }
        }

        $path .= DS.$filename;
        $hand = fopen($path, 'a+');

        fwrite($hand, '<div>Olá mundo!</div>');
        fclose($hand);
    }

    /**
     * @param string $class_name
     * @param OutputInterface $output
     */
    private function callMakeModel($class_name, $output)
    {
        $cmd = 'make:model';
        $command = $this->getApplication()->find($cmd);

        $arguments = [
            'command' => $cmd,
            'name'    => $class_name,
        ];

        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);

        return true;
    }
}
