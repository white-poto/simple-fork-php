<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 15:14
 */

namespace Jenner\SimpleFork\Cache;


/**
 * redis cache
 *
 * @package Jenner\SimpleFork\Cache
 */
class RedisCache implements CacheInterface
{

    /**
     * @var \Redis
     */
    protected $redis;

    protected $prefix;

    /**
     * @param string $host
     * @param int $port
     * @param int $database
     * @param string $prefix
     */
    public function __construct(
        $host = '127.0.0.1',
        $port = 6379,
        $database = 0,
        $prefix = 'simple-fork'
    )
    {
        $this->redis = new \Redis();
        $connection_result = $this->redis->connect($host, $port);
        if (!$connection_result) {
            throw new \RuntimeException('can not connect to the redis server');
        }

        if ($database != 0) {
            $select_result = $this->redis->select($database);
            if (!$select_result) {
                throw new \RuntimeException('can not select the database');
            }
        }

        if (empty($prefix)) {
            throw new \InvalidArgumentException('prefix can not be empty');
        }
        $this->prefix = $prefix;
    }

    /**
     * close redis connection
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * close the connection
     */
    public function close()
    {
        $this->redis->close();
    }

    /**
     * get var
     *
     * @param $key
     * @param null $default
     * @return bool|string|null
     */
    public function get($key, $default = null)
    {
        $result = $this->redis->hGet($this->prefix, $key);
        if ($result !== false) return $result;

        return $default;
    }

    /**
     * set var
     *
     * @param $key
     * @param null $value
     * @return boolean
     */
    public function set($key, $value)
    {
        return $this->redis->hSet($this->prefix, $key, $value);
    }

    /**
     * has var ?
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->redis->hExists($this->prefix, $key);
    }

    /**
     * delete var
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if ($this->redis->hDel($this->prefix, $key) > 0) {
            return true;
        }
        return false;
    }
}