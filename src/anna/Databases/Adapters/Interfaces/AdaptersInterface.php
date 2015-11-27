<?php
namespace Anna\Databases\Adapters\Interfaces;


/**
 * -----------------------------------------------------------
 * DatabaseInterface
 * -----------------------------------------------------------
 *
 * Força implementação das camadas de dados utilizadas pelo usuário dando a opção
 * de escolha quanto usar o ORM preferencial ou usar o PDO diretamente
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 06, novembro 2015
 * @package Anna\Databases\Adapters
 */
interface AdaptersInterface
{

    /**
     * Inicializa a camada de dados carregando as configurações
     */
    function init();

    /**
     * Ativa a conecção com o banco de dados
     */
    function getManager();

}
