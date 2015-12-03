<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Databases\Adapters\DoctrineAdapter;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * ---------------------------------------
 * Class MakeExportCommand
 * ---------------------------------------.
 *
 * Exporta os modelos da aplicação para tabelas ainda não existentes no banco de dados configurado
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 16/11/2015
 */
class DbExportCommand extends Command
{
    /**
     * configure your command here.
     */
    protected function configure()
    {
        $this->setName('db:export');
        $this->setDescription('Atualiza o banco de dados com as entidades presentes na pasta App\Models');

        $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Efetua update dos modelos ja existentes no banco de dados.');
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

                if ($schemaManager->tablesExist([$table_name]) != true) {
                    $models[] = $metadata;
                }
            }

            if (count($models) > 0) {
                $schema->createSchema($models);
            } else {
                $output->writeln('Sem Models para exportar');
            }

            if ($input->getOption('update')) {
                $models = [];
                foreach ($classes as $class) {
                    $metadata = $em->getClassMetadata($class);
                    $table_name = $metadata->getTableName();

                    if ($schemaManager->tablesExist([$table_name]) == true) {
                        $models[] = $metadata;
                    }
                }

                if (count($models) > 0) {
                    $schema->updateSchema($models);
                } else {
                    $output->writeln('Sem models para atualizar');
                }
            }
        } catch (\Doctrine\ORM\Mapping\MappingException $e) {
            $output->writeln('Erro encontrado:');
            $output->writeln($e->getMessage());
        }

        $output->writeln('Banco de dados sincronizado com sucesso.');
    }
}
