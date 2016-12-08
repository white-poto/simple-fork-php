<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 15:03
 */

namespace Jenner\SimpleFork\Queue;

/**
 * redis queue
 *
 * @package Jenner\SimpleFork\Queue
 */
class RedisQueue implements QueueInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var string redis key of queue
     */
    protected $channel;

    /**
     * @param string $host redis server host
     * @param int $port redis server port
     * @param int $database redis server database num
     * @param string $channel redis queue key
     * @param string $prefix prefix of redis queue key
     */
    public function __construct(
        $host = '127.0.0.1',
        $port = 6379,
        $database = 0,
        $channel = 'cache',
        $prefix = 'simple-fork-'
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

        if (empty($channel)) {
            throw new \InvalidArgumentException('channel can not be empty');
        }

        $this->channel = $channel;

        if (empty($prefix)) return;

        $set_option_result = $this->redis->setOption(\Redis::OPT_PREFIX, $prefix);
        if (!$set_option_result) {
            throw new \RuntimeException('can not set the \Redis::OPT_PREFIX Option');
        }
    }

    /**
     * put value into the queue
     *
     * @param $value
     * @return bool
     */
    public function put($value)
    {

        if ($this->redis->lPush($this->channel, $value) !== false) {
            return true;
        }

        return false;
    }

    /**
     * get value from the queue
     *
     * @param bool $block if block when the queue is empty
     * @return bool|string
     */
    public function get($block = false)
    {
        if (!$block) {
            return $this->redis->rPop($this->channel);
        } else {
            while (true) {
                $record = $this->redis->rPop($this->channel);
                if ($record === false) {
                    usleep(1000);
                    continue;
                }

                return $record;
            }
        }
    }

    /**
     * get the size of the queue
     *
     * @return int
     */
    public function size()
    {
        return $this->redis->lSize($this->channel);
    }

    /**
     * remove the queue resource
     *
     * @return mixed
     */
    public function remove()
    {
        return $this->redis->delete($this->channel);
    }

    /**
     * close the connection
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
}