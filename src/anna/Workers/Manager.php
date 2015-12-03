<?php

namespace Anna\Workers;

use Anna\Error;
use Anna\Helpers\LogHelper;
use Anna\Workers\Abstracts\Worker;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * -------------------------------------------------------------
 * Manager
 * -------------------------------------------------------------.
 *
 * Classe gerenciador do serviço de workers, efetua as consultas à tabela e invoca os workers quando for a hora
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 23, Novembro 2015
 */
final class Manager
{
    private $workers = [];

    public function run()
    {
        $this->warmUp();
    }

    /**
     * Teste de documentação do método para exibição no prompt.
     */
    public function registerWorkers()
    {
        $this->loadWorkers();

        $table = Table::getInstance();
        $table->registerWorkers($this->workers);
        $table->save();
    }

    public function updateWorkers()
    {
        $table = Table::getInstance();
        $this->workers = [];
        $this->loadWorkers();

        $table->updateWorkers($this->workers);
    }

    private function warmUp()
    {
        $table = Table::getInstance();
        $workers = $table->getWorkers();
        $now = time();

        foreach ($workers as $worker) {
            if ($worker['active'] == true) {
                $exec_time = strtotime($worker['next_run']);
                $active_in = strtotime($worker['active_in']);

                if ($now >= $exec_time && $now >= $active_in) {
                    $this->doWork($worker); //em outro processo em background;

                    $worker['times_performed'] = (int) $worker['times_performed'] + 1;
                    $worker['last_run'] = $worker['next_run'];

                    if ($worker['limit_performation']) {
                        if ($worker['times_performed'] >= $worker['limit_performation']) {
                            $worker['active'] = false;
                        }
                    }

                    if ($worker['active'] == true) {
                        $next_date = new \Datetime('now');
                        $next_run = $this->getNextExecutionTime($worker['string_timed'], $next_date)->format('Y-m-d H:i:s');
                        $worker['next_run'] = $next_run;
                    }

                    $table->updateWorker($worker);
                }
            }
        }
    }

    private function doWork($worker)
    {
        $classname = $worker['class_name'];

        try {
            $ref = new \ReflectionClass($classname);
        } catch (\ReflectionException $e) {
            $error = new Error();
            $error->logProduction($e);

            return;
        }

        $worker = $ref->newInstance();

        $worker->execute();
    }

    private function loadWorkers()
    {
        $path = SYS_ROOT.'App'.DS.'Workers'.DS;

        if (!is_dir($path)) {
            throw new \Exception("A pasta App\Workers nao existe.");
        }

        $class_workers = $this->loadAppWorkers($path);

        foreach ($class_workers as $worker) {
            try {
                $reflection = new \ReflectionClass($worker);
            } catch (\ReflectionException $e) {
                $logHelper = new LogHelper();
                $logHelper->writeln($e->getMessage());
                die();
            }

            $worker = $reflection->newInstance();
            $worker->configure();
            $this->workers[] = $this->getWorkerConfig($worker);
        }
    }

    /**
     * Pega a configuração do do worker para registro.
     * 
     * @param Worker $worker
     *
     * @return array
     */
    private function getWorkerConfig(Worker $worker)
    {
        $config = [];
        $config['name'] = $worker->getWorkerName();
        $config['start_to_work'] = $worker->getStartWorkTime();
        $config['string_timed'] = $worker->getStringTimed();
        $config['execution_number'] = $worker->getExecutionNumber();
        $config['active'] = $worker->isActived();
        $config['next_execution_time'] = $this->getNextExecutionTime($config['string_timed'], $config['start_to_work']);
        $config['class_name'] = get_class($worker);

        return $config;
    }

    /**
     * Carrega os comandos criandos pelos desenvolvedores inicialização.
     *
     * @param string $path
     * @return array
     */
    private function loadAppWorkers($path)
    {
        $fqcns = [];

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
                    $index += 2; // Pula namespace e espaços em branco
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }

                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Pula palavra chave 'class' e espaços em branco
                    $fqcns[] = $namespace.'\\'.$tokens[$index][1];
                }
            }
        }

        $lista_final = array_filter($fqcns, function ($item) {
            preg_match('~Worker~', $item, $teste);

            return (count($teste)) ? true : false;
        });

        return $lista_final;
    }

    private function getNextExecutionTime($string_timed, \DateTime $start_to_work_time)
    {
        if (!$string_timed) {
            throw new \Exception('Não há tempo de ativação configurado.');
        }

        preg_match_all('/([0-9]+\w{1})/', $string_timed, $results);

        if (is_array($results[1]) && count($results[1]) > 0) {
            $start_time = $start_to_work_time;
            $next_time = $start_time->getTimestamp();

            foreach ($results[1] as $time) {
                preg_match('/\d+/', $time, $testDigit);
                if (!is_array($testDigit) || count($testDigit) <= 0 || empty($testDigit[0])) {
                    throw new \Exception('String de periodicidade mal configurada.');
                }

                $amount = $testDigit[0];

                preg_match('/[a-zA-Z]/', $time, $testChar);
                if (!is_array($testDigit) || count($testDigit) <= 0 || empty($testDigit[0] || !in_array($testChar[0], ['y', 'M', 'w', 'd', 'h', 'm', 's']))) {
                    throw new \Exception('String de periodicidade mal configurada.');
                }

                $periodicity = $testChar[0];
                $period = '';

                switch ($periodicity) {
                    case 'y':
                        $period = 'years';
                        break;

                    case 'M':
                        $period = 'months';
                        break;

                    case 'w':
                        $period = 'weeks';
                        break;

                    case 'd':
                        $period = 'days';
                        break;

                    case 'h':
                        $period = 'hours';
                        break;

                    case 'm':
                        $period = 'minutes';
                        break;

                    case 's':
                        $period = 'seconds';
                        break;

                    default:
                    throw new \Exception('String de periodicidade mal configurada.');
                }

                $add_string = "+$amount $period";
                $next_time = strtotime($add_string, $next_time);
            }

            return new \DateTime(date('Y-m-d H:i:s', $next_time));
        } else {
            throw new \Exception('String de periodicidade mal configurada.');
        }
    }
}
