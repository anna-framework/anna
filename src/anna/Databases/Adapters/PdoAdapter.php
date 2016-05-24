<?php

namespace Anna\Databases\Adapters;

use Anna\Config;
use Anna\Databases\Adapters\Interfaces\AdaptersInterface;
use Anna\Error;
use \PDO as PDO;
use \PDOException as PDOException;

/**
 * Class PdoAdapter
 *
 * Classe que efetiva a conexão com banco de dados utilizando-se da biblioteca PDO
 *
 * @package Anna\Databases\Adapters
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 24, maio 2016
 */
class PdoAdapter implements AdaptersInterface
{

    /**
     * @var \PDO
     */
    private $conn;

    public function init()
    {
        $connData = $this->loadConfiguration();

         try {
            $pdo = new PDO("mysql:host=" . $connData['host'] . ";dbname=" . $connData['dbname'], $connData['user'], $connData['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,'SET NAMES UTF8');

         } catch (PDOException $e) {
             $error = new Error();
             $error->log($e);
         }

        $this->conn = $pdo;
    }

    /**
     * @return PDO
     */
    public function getManager()
    {
        return $this->conn;
    }

    private function loadConfiguration()
    {
        $config = Config::getInstance();
        $driver = $config->get('database.connection.driver');

        switch ($driver) {
            case 'pdo_mysql':
                $conn_params = [
                    'driver'           => $driver,
                    'host '            => $config->get('database.connection.host'),
                    'user'             => $config->get('database.connection.user'),
                    'password'         => $config->get('database.connection.password'),
                    'dbname'           => $config->get('database.connection.db_name'),
                    'charset'          => $config->get('database.connection.charset'),
                    'port'             => $config->get('database.connection.port'),
                    'collation'        => $config->get('database.connection.collation'),
                    'prefix'           => $config->get('database.connection.prefix'),
                    'unix_socket'      => $config->get('database.connection.unix_socket'),
                ];
                break;
            case 'pdo_pgsql':
                $conn_params = [
                    'driver'          => $driver,
                    'host'            => $config->get('database.connection.host'),
                    'port'            => $config->get('database.connection.port'),
                    'user'            => $config->get('database.connection.user'),
                    'password'        => $config->get('database.connection.password'),
                    'db_name'         => $config->get('database.connection.db_name'),
                    'charset'         => $config->get('database.connection.charset'),
                    'sslmode'         => $config->get('database.connection.sslmode'),
                    'sslrootcert'     => $config->get('database.connection.sslrootcert'),
                ];
                break;
            default:
                throw new \Exception("Erro na configuração do banco de dados, verifique o arquivo Config\database.php");
        }

        return $conn_params;
    }
}
