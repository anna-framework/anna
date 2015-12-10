<?php

namespace Anna\Repositories\Abstracts;

use Anna\Config;
use Anna\Repositories\Interfaces\RepositoryInterface;

/**
 * ------------------------------------------------------
 * Abstract Class Repository
 * ------------------------------------------------------.
 *
 * Classe responsável por fornecer a cama de dados do sistema através do padrão Repository
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 06, novembro 2015
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var Anna\Databases\Adapters\Interfaces\AdaptersInterface
     */
    protected $manager;

    public function __construct()
    {
        $this->init();
    }

    /**
     * Inicializa o manager do adaptador registrado nas configurações.
     */
    public function init()
    {
        $adapter_name = Config::getInstance()->get('database.adapter');

        $adapter = new $adapter_name();
        $adapter->init();

        $this->manager = $adapter->getManager();
    }
}
