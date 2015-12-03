<?php


namespace Anna\Workers\Abstracts;

use Anna\Workers\Interfaces\WorkerInterface;

/**
 * -------------------------------------------------------------
 * Worker
 * -------------------------------------------------------------.
 *
 * Classe abstrata worker, que fornece ao gerenciador ferramentas para utilização dos workers que obrigatóriamente
 * devem extender esta classe.
 * 
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 23, Novembro 2015
 */
abstract class Worker implements WorkerInterface
{
    private $string_timed;
    private $worker_name;
    private $start_to_work;
    private $active = false;
    private $exec_number = 0;

    /**
     * Retorna a quantidade de vezes que esse worker será executado.
     *
     * @return int
     */
    public function getExecutionNumber()
    {
        return $this->exec_number;
    }

    /**
     * Ativa ou desativa o worker.
     * 
     * @param bool $bool
     */
    public function setActive($bool = true)
    {
        $this->active = $bool;
    }

    /**
     * Retorna se este worker está ativo ou não.
     *
     * @return bool
     */
    public function isActived()
    {
        return $this->active;
    }

    /**
     * Retorna a data e hora registrado para inicialização da aplicação.
     *
     * @return \DateTime
     */
    public function getStartToWorkTime()
    {
        return $this->start_to_work;
    }

    /**
     * Retorna o nome do worker, caso não esteja configurado retorna o nome da classe.
     *
     * @return string
     */
    public function getWorkerName()
    {
        return $this->worker_name ? $this->worker_name : strtolower(get_class($this));
    }

    /**
     * Retorna a a data e hora em que o worker irá inicar o primeiro trabalho.
     *
     * @return \DateTime
     */
    public function getStartWorkTime()
    {
        return $this->start_to_work;
    }

    /**
     * Retorna a string de periodicidade.
     *
     * @return string
     */
    public function getStringTimed()
    {
        return $this->string_timed;
    }

    /**
     * Informa a quantidade de vezes que este worker irá rodar, caso não seja informado, ele será rodado
     * sempre de acordo com sua periodicidade;.
     * 
     * @param int $exec_number
     */
    protected function setExecNumber($exec_number)
    {
        $this->exec_number = $exec_number;
    }

    /**
     * Informa qual será a periodicidade em que este worker irá executar e também uma data futura inicial.
     * 
     * @param string   $string         string especial contendo os parametros para execução
     * @param Datetime $start_at       data inicial futura do worker, caso null ele começará imediatamente
     * @param int      $execution_time A quantidade de vezes que este worker será rodado, 0 para sem limites
     */
    protected function setActivationTime($string, \Datetime $start_at = null, $execution_time = 0)
    {
        $this->string_timed = $string;
        $this->start_to_work = $start_at ? $start_at : new \Datetime('now');
        $this->exec_number = $execution_time;
    }

    /**
     * Informa para o gerenciador o nome deste worker, caso não seja informado o gerenciador utilizará o nome
     * da Classe.
     *
     * @param string $name
     */
    protected function setWorkerName($name)
    {
        $this->worker_name = $name;
    }

    /**
     * Define uma data e hora para que o worker inicie seu trabalho.
     *
     * @param Datetime $date
     */
    protected function setStartToWork(\Datetime $date)
    {
        $this->start_to_work = $date;
    }
}
