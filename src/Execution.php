<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/20
 * Time: 14:20
 */

namespace Jenner\SimpleFork;

use Jenner\SimpleFork\IPC\CacheInterface;
use Jenner\SimpleFork\IPC\QueueInterface;

class Execution
{
    /**
     * @var CacheInterface
     */
    protected $queue;

    /**
     * @var QueueInterface
     */
    protected $cache;

    /**
     * set cache instance
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * set message queue instance
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

}