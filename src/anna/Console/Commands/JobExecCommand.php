<?php

namespace Anna\Console\Commands;

use Anna\Workers\Manager;
use Anna\Console\Commands\Abstracts\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * JobExecCommand
 * ---------------------------------------.
 *
 * Classe responsável por executar o comando ANNA para executar um turno de vigia sobre os workers
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 074, novembro 2015
 */
class JobExecCommand extends Command
{
    protected function configure()
    {
        $this->setName('job:exec');
        $this->setDescription('Executa o worker manager remotamente.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager();
        $manager->run();
        $manager = null;

        unset($manager);
        clearstatcache();
    }
}
