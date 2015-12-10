<?php

namespace Anna\Databases;

/**
 * -----------------------------------------------------------
 * Model
 * -----------------------------------------------------------.
 *
 * Classe responsável por representar determinada tabela do banco de dados, todos os modelos da aplicação devem
 * extender esta classe
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 08, novembro 2015
 * @MappedSuperclass
 */
class Model
{
    /**
     * @Column(type="datetime", nullable=false)
     */
    public $created_at;

    /**
     * @Column(type="datetime", nullable=true)
     */
    public $deleted_at;
}
