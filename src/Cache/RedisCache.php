<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 15:14
 */

namespace Jenner\SimpleFork\Cache;


class RedisCache implements CacheInterface
{

    /**
     * @var \Redis
     */
    protected $redis;

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
        $prefix = ""
    )
    {
        $this->redis = new \Redis();
        $connection_result = $this->redis->connect($host, $port);
        if (!$connection_result) {
            throw new \RuntimeException("can not connect to the redis server");
        }

        if ($database != 0) {
            $select_result = $this->redis->select($database);
            if (!$select_result) {
                throw new \RuntimeException("can not select the database");
            }
        }

        if (empty($prefix)) return;

        $set_option_result = $this->redis->setOption(\Redis::OPT_PREFIX, $prefix);
        if (!$set_option_result) {
            throw new \RuntimeException("can not set the \\Redis::OPT_PREFIX Option");
        }
    }

    /**
     * get var
     * @param $key
     * @param null $default
     * @return bool|mixed
     */
    public function get($key, $default = null)
    {
        $result = $this->redis->get($key);
        if ($result) return $result;

        return $default;
    }

    /**
     * set var
     * @param $key
     * @param null $value
     * @return boolean
     */
    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    /**
     * has var ?
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * delete var
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->redis->del($key);
    }
}