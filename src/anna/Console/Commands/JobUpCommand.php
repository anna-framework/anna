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
 * Classe responsável por executar o comando ANNA para ativar o serviço de workers.
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 074, novembro 2015
 */
class JobUpCommand extends Command
{
    protected function configure()
    {
        $this->setName('job:up');
        $this->setDescription('Ativa o JobObserver a fim de que acione os workers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = Table::getInstance();
        $config = $table->getConfig();

        if ($config['active'] == true) {
            $output->writeln('Servico de workers ja estao ativos, finalize o servico antes de inicialo novamente.');

            return;
        }

        $table->turnOn();

        $os = explode(' ', php_uname())[0];
        if ($os == 'Windows') {
            $WshShell = new \COM('WScript.Shell');
            $WshShell->Run('php anna job:observer', 0, false);
        } else {
            exec('php anna job:observer &');
        }

        $output->writeln('Servico de workers acionado com sucesso.');
    }
}
