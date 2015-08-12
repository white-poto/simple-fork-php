<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:28
 */

namespace Jenner\SimpleFork;

use Jenner\SimpleFork\IPC\CacheInterface;
use Jenner\SimpleFork\IPC\QueueInterface;

abstract class Runnable
{

    protected $queue;

    protected $cache;

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * 进程执行入口
     * @return mixed
     */
    abstract public function run();
}