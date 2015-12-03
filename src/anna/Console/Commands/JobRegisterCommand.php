<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Workers\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * JobRegisterCommand
 * ---------------------------------------.
 *
 * Atualiza a tabela de workers
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 26, novembro 2015
 */
class JobRegisterCommand extends Command
{
    protected function configure()
    {
        $this->setName('job:register');
        $this->setDescription('Registra novos workers e atualiza informações dos antigos a tabela do work manager.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager();
        $manager->registerWorkers();
        $manager = null;

        unset($manager);
        clearstatcache();
    }
}
