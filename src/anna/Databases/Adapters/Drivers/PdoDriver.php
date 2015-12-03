<?php

namespace Anna\Databases\Adapters\Drivers;

use Anna\Error;
use Anna\Helpers\JsonHelper;
use Anna\Helpers\LoggingHelper;

class PdoDriver extends \PDO
{
    private $sQuery;
    private $DBName;
    private $DBPassword;
    private $bConnected = false;
    private $log;
    private $params;
    public $rowCount = 0;
    public $columnCount = 0;
    public $querycount = 0;

    public function __construct($link, $user, $senha, $array)
    {
        $this->log = new LoggingHelper();
        $this->Json = new JsonHelper();
        $this->params = [];

        parent::__construct($link, $user, $senha, $array);
    }

    /**
     * Valida se conexão está aberta e executa funções do drive.
     */
    public function Execute($query, $params = '')
    {
        try {
            $this->params = $params;
            $this->sQuery = $this->prepare($this->BuildParams($query, $this->params));
            if (!empty($this->params)) {
                if (array_key_exists(0, $params)) {
                    $paramsType = true;
                    array_unshift($this->params, '');
                    unset($this->params[0]);
                } else {
                    $paramsType = false;
                }

                foreach ($this->params as $column => $value) {
                    $this->sQuery->bindParam($paramsType ? intval($column) : ':'.$column, $this->params[$column]);
                }
            }

            $this->succes = $this->sQuery->execute();
            $this->querycount++;
        } catch (\PDOException $e) {
            Error::log($e);
        }

        $this->params = [];
    }

    /**
     * Inicia conexão com o driver database PDO.
     */
    private function BuildParams($query, $params = null)
    {
        if (!empty($params)) {
            $rawStatement = explode(' ', $query);
            foreach ($rawStatement as $value) {
                if (strtolower($value) == 'in') {
                    return str_replace('(?)', '('.implode(',', array_fill(0, count($params), '?')).')', $query);
                }
            }
        }

        return $query;
    }

    public function Query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
    {
        $query = trim($query);
        $rawStatement = explode(' ', $query);
        $this->Execute($query, $params);
        $statement = strtolower($rawStatement[0]);
        if ($statement    === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return null;
        }
    }

    public function lastInsertId($seqname = null)
    {
        return $this->lastInsertId();
    }

    public function Column($query, $params = null)
    {
        $this->Execute($query, $params);
        $resultColumn = $this->sQuery->fetchAll(\PDO::FETCH_COLUMN);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();

        return $resultColumn;
    }

    public function Row($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
    {
        $this->Execute($query, $params);
        $resultRow = $this->sQuery->fetch($fetchmode);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();

        return $resultRow;
    }

    public function Single($query, $params = null)
    {
        $this->Execute($query, $params);

        return $this->sQuery->fetchColumn();
    }

    public function ExceptionLog($message, $sql = '')
    {
        if (!empty($sql)) {
            $message .= "\r\nRaw SQL : ".$sql;
        }

        $this->log->write($message, $this->DBName.md5($this->DBPassword));

        if (isset($_SESSION['admin'])) {
            $message = $message;
        } else {
            $message = 'Algum erro ocorreu, tente novamente ou contate um administrador.';
        }

        $message = ['critical' => $message];

        return $this->Json->encode($message);
    }

    /**
     * Fecha conexão com o PDO.
     */
    public function CloseConnection()
    {
        $this->pdo = null;
    }
}
