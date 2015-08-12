<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:25
 */

namespace Jenner\SimpleFork;


use Jenner\SimpleFork\IPC\CacheInterface;
use Jenner\SimpleFork\IPC\QueueInterface;

class Process
{
    /**
     * @var IPC\CacheInterface
     */
    protected $cache;

    /**
     * @var IPC\QueueInterface
     */
    protected $queue;

    /**
     * @var Runnable
     */
    protected $runnable;

    protected $pid = 0;

    protected $running = false;

    protected $status = 0;

    /**
     * @param Runnable $runnable
     */
    public function __construct(Runnable $runnable)
    {
        $this->runnable = $runnable;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function isRunning()
    {
        return $this->running;
    }

    public function exitCode()
    {
        return $this->status;
    }

    /**
     * @return string pid
     */
    public function start()
    {
        $callback = null;
        if (is_object($this->runnable)) {
            $callback = array($this->runnable, 'run');
        } else {
            $callback = array($this, 'run');
        }

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        } elseif ($pid > 0) {
            $this->pid = $pid;
            $this->running = true;
        } else {
            call_user_func($callback);
        }
    }

    public function stop()
    {
        if (!posix_kill($this->pid, SIGTERM)) {
            throw new \RuntimeException("kill son process failed");
        }

        if (pcntl_waitpid($this->pid, $this->status) == -1) {
            throw new \RuntimeException("wait son process failed");
        }
    }

    /**
     *
     */
    public function run()
    {
    }
}