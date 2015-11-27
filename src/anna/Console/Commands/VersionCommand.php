<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class VersionCommand
 * ---------------------------------------
 *
 * Exibe a versão da ANNA
 *
 * @package Anna\Console\Commands
 */
class VersionCommand extends Command
{

    protected function configure()
    {
        $this->setName('version');
        $this->setDescription('Exibe a versao do anna');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $name = Config::getInstance()->get('app.developer');
        $text = 'Ola ' . $name . ', estou na versao 1.1';

        $output->writeln($text);
    }

}
