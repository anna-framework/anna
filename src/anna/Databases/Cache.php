<?php

namespace Anna\Databases;

use Anna\Config;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\XcacheCache;

/**
 * -----------------------------------------------------------
 * Cache
 * -----------------------------------------------------------.
 *
 * Utiliza o driver de cache configurado para realizar consultas e persistências no cache
 *
 * @author Cristiano Gomes <cmgomes.es@gmail.com>
 *
 * @since 18, novembro 2015
 */
class Cache
{
    /**
     * Instancia do driver selecionado a partir das configurações.
     *
     * @var CacheProvider
     */
    private $driver;

    private static $instance;

    public static function getInstance()
    {
        $config = Config::getInstance()->get('cache');

        if (!$config['cache']) {
            throw new \Exception('Driver de cache não configurado.');
        }

        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $config = Config::getInstance()->get('cache');

        if (!$config['cache']) {
            throw new \Exception('Driver de cache não configurado.');
        }

        $this->selectDriver($config);
    }

    public function selectDriver($config)
    {
        $cache_engine = $config['cache-engine'];

        //redis, apc, memcache, memcached e xcache
        switch ($cache_engine) {
            case 'redis':
                if (!extension_loaded('redis')) {
                    throw new \Exception("Extensão para $cache_engine não encontrada");
                }

                $redis = new \Redis();
                $redis->connect($config['cache-config']['host'], $config['cache-config']['port']);

                $this->driver = new RedisCache();
                $this->driver->setRedis($redis);
                break;

            case 'apc':
                if (!extension_loaded('apc')) {
                    throw new \Exception("Extensão para $cache_engine não encontrada");
                }

                $this->driver = new ApcCache();
                break;

            case 'memcache':
                if (!extension_loaded('memcache')) {
                    throw new \Exception("Extensão para $cache_engine não encontrada");
                }

                $memcache_config = new \Memcache();
                $memcache_config->connect($config['cache-config']['host'], $config['cache-config']['port']);

                $this->driver = new MemcacheCache();
                $this->driver->setMemcache($memcache_config);
                break;

            case 'memcached':
                if (!extension_loaded('memcache')) {
                    throw new \Exception("Extensão para $cache_engine não encontrada");
                }

                $memcached = new \Memcached();
                $memcached->addServer($config['cache-config']['host'], $config['cache-config']['port']);

                $this->driver = new MemcachedCache();
                $this->driver->setMemcached($memcached);
                break;

            case 'xcache':
                if (!extension_loaded('xcache')) {
                    throw new Exception("Extensão para $cache_engine não encontrada");
                }
                $this->driver = new XcacheCache();
                break;

            default:
                throw new \Exception('O driver especificado não foi encontado.');
        }
    }

    public function save($id, $data, $life = null)
    {
        $this->driver->save($id, $data, $life);
    }

    public function contains($id)
    {
        return $this->driver->contains($id);
    }

    public function fetch($id)
    {
        return $this->driver->fetch($id);
    }

    public function delete($id)
    {
        return $this->driver->delete($id);
    }

    public function deleteAll()
    {
        return $this->driver->deleteAll();
    }

    /**
     * Retorna o driver de cache utilizado pelo sistema.
     *
     * @return CacheProvider
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
