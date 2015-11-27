<?php
namespace Anna\Repositories\Interfaces;

use Anna\Databases\Model;

/**
 * -----------------------------------------------------------
 * RepositoryInterface
 * -----------------------------------------------------------
 *
 * Força implementação do método init, utilizado para inicializar as configurações de conexão 
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 06, novembro 2015
 * @package Anna\Repositories\Interfaces
 */
interface RepositoryInterface 
{
    function init();
}
