<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Databases\Adapters\DoctrineAdapter;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class DbDropTableCommand
 * ---------------------------------------.
 *
 * Remote a tabela referente a entidade recebida como parametro
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 16/11/2015
 */
class DbDropTableCommand extends Command
{
    protected function configure()
    {
        $this->setName('db:drop-table');
        $this->setDescription('Dropa a tabela do model especificado do banco de dados');
        $this->addArgument('model_name', InputArgument::REQUIRED, 'Nome do model a ser removido do banco de dados');
    }

    /**
     * do the thing here.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = new DoctrineAdapter();
        $driver->init();
        $em = $driver->getManager();
        $schema = new SchemaTool($em);
        $models = [];

        $model_name = $input->getArgument('model_name');
        $full_name = Config::getInstance()->get('root-namespace').'\\Models'.'\\'.$model_name.'Model';

        $schemaManager = $em->getConnection()->getSchemaManager();
        try {
            $metadata = $em->getClassMetadata($full_name);
            $table_name = $metadata->getTableName();

            if ($schemaManager->tablesExist([$table_name]) == true) {
                $models[] = $metadata;
            }

            if (count($models) > 0) {
                $schema->dropSchema($models);
            } else {
                $output->writeln('O model '.$full_name.' nao esta registrado no banco de dados.');
            }
        } catch (\Doctrine\ORM\Mapping\MappingException $e) {
            $output->writeln('Erro encontrado:');
            $output->writeln($e->getMessage());

            return;
        }

        $output->writeln('Tabela '.$table_name.' removida com sucesso.');
    }
}
