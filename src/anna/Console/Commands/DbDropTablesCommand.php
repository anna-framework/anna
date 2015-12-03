<?php

namespace Anna\Console\Commands;

use Anna\Console\Commands\Abstracts\Command;
use Anna\Databases\Adapters\DoctrineAdapter;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

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

        $classes = $this->loadAppModels();
        $schema = new SchemaTool($em);
        $models = [];

        try {
            $schemaManager = $em->getConnection()->getSchemaManager();

            foreach ($classes as $class) {
                $metadata = $em->getClassMetadata($class);
                $table_name = $metadata->getTableName();

                if ($schemaManager->tablesExist([$table_name]) == true) {
                    $models[] = $metadata;
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

    /**
     * Carrega os models criados pelo desenvolvedor.
     *
     * @return array
     */
    private function loadAppModels()
    {
        $fqcns = [];
        $path = SYS_ROOT.'App'.DS.'Models'.DS;

        $all_files = new \RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        $php_files = new \RegexIterator($all_files, '/\.php$/');

        foreach ($php_files as $php_file) {
            $content = file_get_contents($php_file->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';

            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }

                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Pula namespace e espaรงos em branco
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }

                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Pula palavra chave 'class' e espaรงos em branco
                    $fqcns[] = $namespace.'\\'.$tokens[$index][1];
                }
            }
        }

        $lista_final = array_filter($fqcns, function ($item) {
            preg_match('~Model~', $item, $teste);

            return (count($teste)) ? true : false;
        });

        return $lista_final;
    }
}
