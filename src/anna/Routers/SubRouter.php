<?php

namespace Anna\Routers;

use Anna\Routers\Interfaces\RouterInterface;
use Anna\Routers\Traits\RouterTrait;

/**
 * -------------------------------------------------------------
 * SubRouter
 * -------------------------------------------------------------.
 *
 * Subroteador, utilizado para criar rotas que estão localizadas dentro de agrupamentos
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 13, Novembro 2015
 */
class SubRouter implements RouterInterface
{
    use RouterTrait;

    /**
     * Retorna a Coleção de rotas registradas.
     *
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Retorna uma nova instância de SubRouter para uso em prefixos ou watchers.
     *
     * @return SubRouter
     */
    public function getSubRouter()
    {
        return new self();
    }
}
