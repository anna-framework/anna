<?php
namespace Anna\Databases\Adapters;

use Anna\Config;
use Anna\Databases\Adapters\Config\DoctrineTablePrefix;
use Anna\Databases\Adapters\Interfaces\AdaptersInterface;
use Anna\Databases\Adapters\Drivers\PdoDriver;
use Anna\Error;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;

/**
 * -----------------------------------------------------------
 * PdoAdapter
 * -----------------------------------------------------------
 *
 * Adaptador para o objeto PDO
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 * @since 11, novembro 2015
 * @package Anna\Databases\Adapters
 */
class PdoAdapter implements AdaptersInterface 
{
	/**
	 * @var PdoDriver
	 */
	private $pdo;

	/**
	 * Executa as configurações iniciais e prepara o a entidade responsáveç
	 * da biblioteca escolhida para ORM
	 */
	public function init() 
    {
		$config = Config::getInstance();
		$paths = [SYS_ROOT . 'App' . DS . 'Models' . DS];

		// the connection configuration
		$conn_params = [
			'host'	   => $config->get('database.connection.host'),
		    'driver'   => $config->get('database.connection.driver'),
		    'user'     => $config->get('database.connection.user'),
		    'password' => $config->get('database.connection.password'),
		    'dbname'   => $config->get('database.connection.db_name'),
			'charset'  => $config->get('database.connection.charset')
		];
        
		try {
			$this->pdo = new PdoDriver('mysql:dbname=' . $conn_params['dbname'] . ';host=' .  $conn_params['host'] . '',
				 $conn_params['user'],
				 $conn_params['password'],
				[
					\PDO::MYSQL_ATTR_INIT_COMMAND 		=> "SET NAMES ".$conn_params['charset'],
					\PDO::ATTR_EMULATE_PREPARES 			=> false,
					\PDO::ATTR_ERRMODE 					=> \PDO::ERRMODE_EXCEPTION,
					\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY 	=> true,
					\PDO::ATTR_PERSISTENT 				=> true
				]
			);
			$this->bConnected = true;
		} catch (\PDOException $e) {
            Error::log($e)
		}
	}

	/**
	 * @return PdoDriver
	 */
	public function getManager()
    {
		return $this->pdo;
	}

}
