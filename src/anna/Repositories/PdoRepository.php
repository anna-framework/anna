<?php

namespace Anna\Repositories;

use Anna\Repositories\Abstracts\Repository;

/**
 * Class PdoRepository.
 *
 * Implementa repositório utilizando-se da biblioteca PDO
 *
 * Trabalha-se de forma diferente, utilizando-se de queries manuais e mapeando os dados de saída pelos model's
 * informados, retornando stdClass quando não há models informados. Pode-se utilizar o mecanismo de queries por
 * arquivo, onde as queries são escritas em uma pasta em arquivos diferentes e acessadsa pelo repositório pelo nome
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 24, maio 2016
 */
class PdoRepository extends Repository
{
}
