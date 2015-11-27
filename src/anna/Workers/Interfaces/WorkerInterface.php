<?php

namespace Anna\Workers\Interfaces;

/**
 * -------------------------------------------------------------
 * WorkerInterface
 * -------------------------------------------------------------
 *
 * Interface indicadora para utilização de workers.
 *
 * Workers são classes trabalhadoras, que irão rodar de tempo em tempo conforme definido por si mesmas no método configure()
 * Para por os workers para trabalhar basta rodar o comando anna: php anna job:up
 * 
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 23, Novembro 2015
 * @package Anna\Workers\Interfaces
 */
interface WorkerInterface
{
    function configure();
    function execute();
}
