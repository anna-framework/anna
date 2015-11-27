<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;

use Anna\Workers\Manager;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use Anna\Console\Helpers\TemplateHelper;

/**
 * ---------------------------------------
 * JobUpdateCommand
 * ---------------------------------------
 *
 * Classe responsável por executar o comando ANNA para criar um novo comando na aplicação do desenvolvedor
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 074, novembro 2015
 * @package Anna\Console\Commands
 */
class JobUpdateCommand extends Command
{
 
    protected function configure() 
    {
        $this->setName('job:update');
        $this->setDescription('Atualiza a tabela do work manager.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $manager = new Manager();
        $manager->updateWorkers();
        $manager = null;

        unset($manager);
        clearstatcache();
    }

}
