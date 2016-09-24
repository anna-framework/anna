<?php

namespace Anna\Repositories;

use Anna\Config;
use Anna\Databases\Model;
use Anna\Error;
use Anna\Request;

/**
 * -----------------------------------------------------------
 * Repository
 * -----------------------------------------------------------.
 *
 * Repositório padrão, fornecido pelo sistema para trabalhar com o adaptador original do Doctrine2 ORM cadastrado
 * nas configurações
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 06, novembro 2015
 */
class PDORepository extends Abstracts\Repository
{
    /**
     * @var \PDO
     */
    protected $manager;

    /**
     * Efetua a busca na base a partir da query informada
     *
     * @param $sqlScript
     * @param array $params
     * @param bool $fetch_one
     * @return array|mixed
     */
    public function query($sqlScript, array $params = [], $fetch_one = false) {
        $sql = $this->getScript($sqlScript);

        return $this->getResult($sql, $params, $fetch_one);
    }

    /**
     * Efetua busca dinâmica pela tabela informada com os valores passados via parâmetros
     *
     * @param $tableName
     * @param array $params
     * @param bool $fetch_one
     * @return array|mixed
     */
    public function search($tableName, $params = [], $fetch_one = false) {
        $sql = "SELECT * FROM $tableName WHERE 1=1 ";

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $sql .= " AND $key = :$key ";
            }
        }

        return $this->getResult($sql, $params, $fetch_one);
    }

    /**
     * Busca registro a partir da chave primária, não funciona com chave composta
     *
     * @param $tablename    Nome da tabela
     * @param $id   Valor da chave primária
     * @return array|mixed
     * @throws \DatabaseException
     */
    public function findOne($tablename, $id) {

        $sql = "SHOW KEYS FROM $tablename WHERE Key_name = 'PRIMARY'";
        $res = $this->getResult($sql, [], true);
        if (!count($res) > 0) {
            throw new \DatabaseException("Chave primária da tabela [$tablename] não encontrada");
        }

        $primary = $res['Column_name'];
        $sql = "SELECT * FROM $tablename WHERE $primary = $id";

        return $this->getResult($sql, [], true);
    }

    /**
     * Prerara, executa e retorna os resultados de queries
     *
     * @param $sql
     * @param $params
     * @param bool $fetch_one
     * @return array|mixed
     */
    private function getResult($sql, $params = [], $fetch_one = false){
        $query = $this->prepare($sql, $params);
        $query->execute();

        if ($fetch_one) {
            $result = $query->fetch();
        } else {
            $result = $query->fetchAll();
        }

        return $result;
    }

    /**
     * Efetua a leitura do arquivo de script SQL
     *
     * @param $fileScript
     * @return mixed
     * @throws \DatabaseException
     */
    private function getScript($fileScript) {
        $file = Config::getInstance()->get('database.queries_folder') . DS . $fileScript . '.php';

        if (!is_file($file)) {
            throw new \DatabaseException("Arquivo de query [$fileScript] não encontrado");
        }

        return include $file;
    }

    /**
     * Adiciona os parametros recebidos na query
     *
     * @param $sql
     * @param $params
     * @return \PDOStatement
     * @internal param \PDOStatement $query
     */
    private function prepare($sql, $params){

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    if ($this->isNumericArray($value)) {
                        $value = "(" . implode(".", $value) . ")";
                    } else {
                        $value = "('" . implode("'.'", $value) . "')";
                    }
                    $sql = str_replace(':' . $key, $value, $sql);
                }
            }
        }

        $query = $this->manager->prepare($sql);

        foreach ($params as $key => $value) {
            $query->bindParam($key, $value, \PDO::PARAM_INT);
        }

        return $query;
    }

    /**
     * Verifica se o array contém apenas valores numéricos
     *
     * @param $array
     * @return bool
     */
    private function isNumericArray($array){
        foreach ($array as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }
        return true;
    }
}
