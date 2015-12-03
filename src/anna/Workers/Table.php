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

    public function loadInformation()
    {
        $file = file_get_contents(__DIR__.DS.'table.json');
        $this->information = json_decode($file, true);
    }

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

    public function registerWorkers($workers)
    {
        foreach ($workers as $worker) {
            $this->registerWorker($worker);
        }
        $this->save();
    }

    public function unregisterWorker($name)
    {
        for ($i = 0; $i < count($this->information['workers']); $i++) {
            if ($this->information['workers'][$i]['name'] == $name) {
                unset($this->information['workers'][$i]);
            }
        }
    }

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
         * @param  string $name
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

            return;
        }

    public function getWorkers()
    {
        return $this->information['workers'];
    }

    public function clearWorkers()
    {
        $this->information['workers'] = [];
        $this->changed = true;
        $this->save();
    }

    public function escreve($text)
    {
        $file = SYS_ROOT.'logs'.DS.'teste.txt';
        $a = fopen($file, 'a+');
        fwrite($a, $text.EOL.EOL);
        fclose($a);
    }

    public function turnOff()
    {
        $this->information['config']['active'] = false;
        $this->changed = true;
        $this->save();
    }

    public function turnOn()
    {
        $this->information['config']['active'] = true;
        $this->changed = true;
        $this->save();
    }

    public function updateMemoryUsage($amount)
    {
        $this->information['config']['memory_usage'] = $amount;
        $this->changed = true;
        $this->save();
    }
}
