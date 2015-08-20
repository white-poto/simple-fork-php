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
     * @var array
     */
    protected $callbacks = array();

    /**
     * event name of before process start
     */
    const BEFORE_START = "beforeStart";

    /**
     * event name of before process exit
     */
    const BEFORE_EXIT = "beforeExit";

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

    /**
     * register callback functions
     * @param $event
     * @param $function
     */
    public function on($event, $function)
    {
        if(!is_callable($function)){
            throw new \LogicException("the callback function is not callable");
        }

        $this->callbacks[$event] = $function;
    }

}