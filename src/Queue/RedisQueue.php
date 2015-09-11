<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 15:03
 */

namespace Jenner\SimpleFork\Queue;

class RedisQueue implements QueueInterface
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
     * put value into the queue of channel
     * @param $channel
     * @param $value
     * @return mixed
     */
    public function put($channel, $value)
    {
        return $this->redis->lPush($channel, $value);
    }

    /**
     * get value from the queue of channel
     * @param $channel
     * @return mixed
     */
    public function get($channel)
    {
        return $this->redis->rPop($channel);
    }

    /**
     * get the size of the queue of channel
     * @param $channel
     * @return mixed
     */
    public function size($channel)
    {
        return $this->redis->lSize($channel);
    }

    /**
     * remove the queue resource
     * @return mixed
     */
    public function remove()
    {
        return $this->redis->flushDB();
    }
}