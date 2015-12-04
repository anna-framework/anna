<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Databases\Adapters\DoctrineAdapter;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * Class DbDropTablesCommand
 * ---------------------------------------.
 *
 * Remote todas as tabelas do banco de dados
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 16/11/2015
 */
class DbDropTablesCommand extends Command
{
    protected function configure()
    {
        $this->setName('db:drop-tables');
        $this->setDescription('Dropa todas as tabelas do banco de dados');
    }

    /**
     * do the thing here.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $driver = new DoctrineAdapter();
        $driver->init();
        $em = $driver->getManager();

        $classes = loadAppModels();
        $schema = new SchemaTool($em);
        $models = [];

        try {
            $schemaManager = $em->getConnection()->getSchemaManager();

            foreach ($classes as $class) {
                $metadata = $em->getClassMetadata($class);
                $table_name = $metadata->getTableName();

                if ($schemaManager->tablesExist([$table_name]) == true) {
                    array_push($models, $metadata);
                }
            }

            if (count($models) > 0) {
                $schema->dropSchema($models);
            } else {
                $output->writeln('Nao ha models exportados para o banco de dados');
            }
        } catch (\Doctrine\ORM\Mapping\MappingException $e) {
            $output->writeln('Erro encontrado:');
            $output->writeln($e->getMessage());

            return;
        }

        $output->writeln('Tabelas removidas com sucesso.');
    }
}
