<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Databases\Adapters\DoctrineAdapter;

use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class VersionCommand
 * ---------------------------------------
 *
 * Exibe o nome do
 *
 * @package Anna\Console\Commands
 */
class CacheCleanCommand extends Command
{

    protected function configure() 
    {
        $this->setName('cache:clean');
        $this->setDescription('Limpa o cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {

        $driver = new DoctrineAdapter();
        $driver->init();
        $manager = $driver->getManager();

        $cacheDriver = $manager->getConfiguration()->getResultCacheImpl();

        try {
            if (is_object($cacheDriver)) {
                $deleted = $cacheDriver->deleteAll();
            }
        } catch (\Exception $e) {
            $output->writeln('Falha na conexao com o servidor de cache:');
            $output->writeln($e->getMessage());
        }

        if (isset($deleted)) {
            $output->writeln('Cache limpo com sucesso.');
        }
    }

}
