<?php

namespace Anna\Databases\Adapters;

use Anna\Config;
use Anna\Databases\Adapters\Config\DoctrineTablePrefix;
use Anna\Databases\Adapters\Interfaces\AdaptersInterface;
use Anna\Databases\Cache;
use Anna\Error;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;

/**
 * -----------------------------------------------------------
 * DoctrineAdapter
 * -----------------------------------------------------------.
 *
 * Adaptador do Doctrine ORM para ser utilizado nas camadas de acesso à
 * base de dados
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 06, novembro 2015
 */
class DoctrineAdapter implements AdaptersInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Executa as configurações iniciais e prepara o a entidade responsáveç
     * da biblioteca escolhida para ORM.
     */
    public function init()
    {
        $config = Config::getInstance();

        //$this->$db_config = '';
        $paths = [SYS_ROOT.'App'.DS.'Models'.DS];
        $dev_mode = $config->get('database.debug');

        $conn_params = $this->loadConfiguration();

        $doctrine_config = Setup::createAnnotationMetadataConfiguration($paths, $dev_mode);

        if ($config->get('cache.cache')) {
            try {
                $cache = Cache::getInstance();

                if ($cache instanceof Cache) {
                    $doctrine_config->setResultCacheImpl($cache->getDriver());
                }
            } catch (\Exception $e) {
                $error = new Error();
                $error->log($e);
            }
        }

        //$doctrine_config->setResultCacheImpl($cacheImpl);

        $proxy_dir = SYS_ROOT.'App'.DS.'Models'.DS.'Proxies';

        if (!is_dir($proxy_dir)) {
            if (mkdir($proxy_dir)) {
                $doctrine_config->setProxyDir($proxy_dir);
            }
        }

        $prefix = $config->get('database.connection.table_prefix');

        if ($prefix != '') {
            $evm = new EventManager();
            $table_prefix = new DoctrineTablePrefix($prefix);
            $evm->addEventListener(Events::loadClassMetadata, $table_prefix);

            $this->entityManager = EntityManager::create($conn_params, $doctrine_config, $evm);
        } else {
            $this->entityManager = EntityManager::create($conn_params, $doctrine_config);
        }

        // $cache = $this->entityManager->getConfiguration()->getResultCacheImpl();
        // $deleted = $cacheDriver->deleteAll();
    }

    /**
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->entityManager;
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

            case 'pdo_sqlite':
                $conn_params = [
                    'driver'    => $driver,
                    'user'      => $config->get('database.connection.user'),
                    'password'  => $config->get('database.connection.password'),
                    'path'      => $config->get('database.connection.path'),
                    'memory'    => $config->get('database.connection.memory'),
                ];
                break;

            case 'mysqli':
                $conn_params = [
                    'driver'         => $driver,
                    'host'           => $config->get('database.connection.host'),
                    'port'           => $config->get('database.connection.port'),
                    'user'           => $config->get('database.connection.user'),
                    'password'       => $config->get('database.connection.password'),
                    'db_name'        => $config->get('database.connection.db_name'),
                    'charset'        => $config->get('database.connection.charset'),
                    'prefix'         => $config->get('database.connection.prefix'),
                    'unix_socket'    => $config->get('database.connection.unix_socket'),
                    'driverOptions'  => $config->get('database.connection.driverOptions'),
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

            case 'pdo_oci':
            case 'oci8':
                $conn_params = [
                    'driver'         => $driver,
                    'host'           => $config->get('database.connection.host'),
                    'port'           => $config->get('database.connection.port'),
                    'user'           => $config->get('database.connection.user'),
                    'password'       => $config->get('database.connection.password'),
                    'db_name'        => $config->get('database.connection.db_name'),
                    'charset'        => $config->get('database.connection.charset'),
                    'servicename'    => $config->get('database.connection.servicename'),
                    'service'        => $config->get('database.connection.service'),
                    'pooled'         => $config->get('database.connection.pooled'),
                    'instancename'   => $config->get('database.connection.instancename'),
                ];
                break;

            case 'sqlsrv':
            case 'pdo_sqlsrv':
                $conn_params = [
                    'driver'      => $driver,
                    'host'        => $config->get('database.connection.host'),
                    'port'        => $config->get('database.connection.port'),
                    'user'        => $config->get('database.connection.user'),
                    'password'    => $config->get('database.connection.password'),
                    'db_name'     => $config->get('database.connection.db_name'),
                ];
                break;

            case 'sqlanywhere':
                $conn_params = [
                    'driver'        => $driver,
                    'host'          => $config->get('database.connection.host'),
                    'port'          => $config->get('database.connection.port'),
                    'user'          => $config->get('database.connection.user'),
                    'password'      => $config->get('database.connection.password'),
                    'db_name'       => $config->get('database.connection.db_name'),
                    'server'        => $config->get('database.connection.server'),
                    'persistent'    => $config->get('database.connection.persistent'),
                ];
                break;

            default:
                throw new \Exception("Erro na configuração do banco de dados, verifique o arquivo Config\database.php");
                break;
        }

        return $conn_params;
    }
}
