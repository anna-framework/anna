<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Workers\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * JobUpCommand
 * ---------------------------------------.
 *
 * Classe responsável por executar o comando ANNA para desativar o serviço de workers
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 074, novembro 2015
 */
class JobDownCommand extends Command
{
    protected function configure()
    {
        $this->setName('job:down');
        $this->setDescription('Finaliza a execucao dos workers desabilitando o observer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Table::getInstance()->turnOff();
        $output->writeln('Finalizando o servico de workers');
    }
}
