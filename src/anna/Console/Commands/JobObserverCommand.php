<?php

namespace Anna\Console\Commands;

use Anna\Config;
use Anna\Console\Commands\Abstracts\Command;
use Anna\Workers\Manager;
use Anna\Workers\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ---------------------------------------
 * OverseerCommand
 * ---------------------------------------.
 *
 * Executa o comando ANNA para iniciar o serviço de work manager
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 074, novembro 2015
 */
class JobObserverCommand extends Command
{
    protected function configure()
    {
        $this->setName('job:observer');
        $this->setDescription('Observera o Work Manager durante sua tarefa de execução de workers, nao usar diretamente.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lapse = time();
        $reload = time();

        Table::getInstance()->updateMemoryUsage(0);
        $this->register();

        while (true) {
            $this->watch();
            $time = time();

            if ($time - $lapse >= 2) {
                if ($this->inspect() === false) {
                    return;
                }

                $lapse = time();
            }

            if ($time - $reload >= 10) {
                $this->updateTable();
                $reload = time();
            }

            sleep(1);
        }
    }

    /**
     * Inspeciona o arquivo de configuração do work manager para atualizar algumas configurações.
     * 
     * @return bool
     */
    private function inspect()
    {
        $table = Table::getInstance();
        $table->loadInformation();
        $config = $table->getConfig();

        if ($config['active'] == false) {
            return false;
        }

        $amount = memory_get_usage(true);
        $megabytes = $amount / 1024 / 1024;

        $table->updateMemoryUsage($megabytes);

        //verificando se a memória atual esta´dentro do limite imposto pela configuração da aplicação
        $limit_memory = Config::getInstance()->get('app.work-limit-memory');

        if ($megabytes >= $limit_memory) {
            $table->turnOff();
            exec('php anna job:up');
        }

        $config = null;
        $table = null;
        $amount = null;

        unset($config);
        unset($table);
        unset($amount);

        return true;
    }

    /**
     * Ela atualiza a tabela do work manager.
     *
     * @return bool
     */
    private function updateTable()
    {
        exec('php anna job:update');

        return true;
    }

    /**
     * Vigia os workers para saber se é hora de executar o seu trabalho.
     *
     * @return bool
     */
    private function watch()
    {
        exec('php anna job:exec');

        return true;
    }

    /**
     * Efetua o registro gerao dos workers na tabela do manager.
     *
     * @return bool
     */
    private function register()
    {
        exec('php anna job:register');

        return true;
    }
}
