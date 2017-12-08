<?php

namespace Anna\Workers;

/**
 * -------------------------------------------------------------
 * Table
 * -------------------------------------------------------------.
 *
 * Classe do Serviço de workers, responsável pela leitura e escrita do arquivo table.json
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 23, Novembro 2015
 */
class Table
{
    private static $instance;

    private $information;

    private $changed = false;

    /**
     * Retorna uma instância de Table.
     *
     * @return Anna\Workers\TableTable
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->loadInformation();
    }

    /**
     * Carrega a informação da tabela de workers.
     *
     * @return void
     */
    public function loadInformation()
    {
        $file = file_get_contents(__DIR__.DS.'table.json');
        $this->information = json_decode($file, true);
    }

    /**
     * Registra um worker worker na tabela.
     *
     * @param array $worker conjunto de informações sobre um worker
     *
     * @return void
     */
    public function registerWorker($worker)
    {
        $worker_obj = $this->getWorkerByName($worker['name']);

        if (!$worker_obj) {
            $item = [];
            $item['name'] = $worker['name'];
            $item['string_timed'] = $worker['string_timed'];
            $item['registered_at'] = date('Y-m-d H:i:s');
            $item['active_in'] = $worker['start_to_work']->format('Y-m-d H:i:s');
            $item['times_performed'] = 0;
            $item['last_run'] = null;
            $item['next_run'] = $worker['next_execution_time']->format('Y-m-d H:i:s');
            $item['name'] = $worker['name'];
            $item['active'] = $worker['active'];
            $item['limit_performation'] = $worker['execution_number'];
            $item['class_name'] = $worker['class_name'];

            $this->information['workers'][] = $item;
        } else {
            foreach ($this->information['workers'] as &$infor_worker) {
                if ($worker['name'] == $infor_worker['name']) {
                    $infor_worker['string_timed'] = $worker['string_timed'];
                    $infor_worker['active_in'] = $worker['start_to_work']->format('Y-m-d H:i:s');
                    $infor_worker['active'] = $worker['active'];

                    if ($infor_worker['limit_performation'] != $worker['execution_number']) {
                        $infor_worker['limit_performation'] = $worker['execution_number'];
                        $infor_worker['times_performed'] = 0;
                    }

                    if ($worker['active'] == true && $infor_worker['limit_performation'] == $worker['execution_number']) {
                        $infor_worker['times_performed'] = 0;
                    }

                    $infor_worker['class_name'] = $worker['class_name'];
                }
            }
        }

        $this->changed = true;
        $this->save();
    }

    /**
     * Registra um conjunto de workers de uma vez.
     *
     * @param array $workers
     *
     * @return void
     */
    public function registerWorkers($workers)
    {
        foreach ($workers as $worker) {
            $this->registerWorker($worker);
        }
        $this->save();
    }

    /**
     * Retira um worker da tabela.
     *
     * @param string $name
     *
     * @return void
     */
    public function unregisterWorker($name)
    {
        $count_workers = count($this->information['workers']);
        for ($i = 0; $i < $count_workers; $i++) {
            if ($this->information['workers'][$i]['name'] == $name) {
                unset($this->information['workers'][$i]);
            }
        }
    }

    /**
     * Atualiza um determinado worker na tabela.
     *
     * @param array $worker
     *
     * @return void
     */
    public function updateWorker($worker)
    {
        foreach ($this->information['workers'] as &$infor_worker) {
            if ($worker['name'] == $infor_worker['name']) {
                $infor_worker['active'] = $worker['active'];
                $infor_worker['last_run'] = $worker['last_run'];
                $infor_worker['next_run'] = $worker['next_run'];
                $infor_worker['times_performed'] = $worker['times_performed'];
            }
        }

        $this->changed = true;
        $this->save();
    }

    /**
     * Atualiza um conjunto de workers de uma vez.
     *
     * @param array $workers
     *
     * @return void
     */
    public function updateWorkers($workers)
    {
        foreach ($workers as $worker) {
            foreach ($this->information['workers'] as &$infor_worker) {
                if ($worker['name'] == $infor_worker['name']) {
                    $infor_worker['string_timed'] = $worker['string_timed'];
                    $infor_worker['active'] = $worker['active'];
                    $infor_worker['limit_performation'] = $worker['execution_number'];
                    $infor_worker['active_in'] = $worker['start_to_work']->format('Y-m-d H:i:s');
                }
            }
        }

        $this->save();
    }

    /**
     * Salva a tabela em disco.
     *
     * @return void
     */
    public function save()
    {
        if ($this->changed) {
            $json = json_encode($this->information, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_PRETTY_PRINT);
            $handler = fopen(__DIR__.DS.'table.json', 'w+');
            fwrite($handler, $json);
            fclose($handler);
            $this->changed = false;
        }
    }

    /**
     * Retorna os dados de configuração do observer.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->information['config'];
    }

    /**
     * Retorna uma instancia do worker encontrado ou retorna null.
     *
     * @param string $name
     *
     * @return Worker
     */
    public function getWorkerByName($name)
    {
        foreach ($this->information['workers'] as $worker) {
            if ($worker['name'] == $name) {
                $reflection = new \ReflectionClass($worker['class_name']);
                $worker = $reflection->newInstance();
                $worker->configure();

                if ($worker->getWorkerName() == $name) {
                    return $worker;
                }
            }
        }
    }

    /**
     * Retorna a lista de workers.
     *
     * @return array
     */
    public function getWorkers()
    {
        return $this->information['workers'];
    }

    /**
     * Remote todos os workers da tabela.
     *
     * @return void
     */
    public function clearWorkers()
    {
        $this->information['workers'] = [];
        $this->changed = true;
        $this->save();
    }

    /**
     * Altera a configuração da tabela para desligada, nesse caso os job observer irá parar de rodar.
     *
     * @return void
     */
    public function turnOff()
    {
        $this->information['config']['active'] = false;
        $this->changed = true;
        $this->save();
    }

    /**
     * Seta a configura da tabela para ligada, o job observer ao ler continuará rodando.
     *
     * @return void
     */
    public function turnOn()
    {
        $this->information['config']['active'] = true;
        $this->changed = true;
        $this->save();
    }

    /**
     * Atualiza a quantidade de memória gasta pelo observer.
     *
     * @param floatval $amount
     *
     * @return void
     */
    public function updateMemoryUsage($amount)
    {
        $this->information['config']['memory_usage'] = $amount;
        $this->changed = true;
        $this->save();
    }
}
