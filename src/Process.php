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

    /**
     * @var int
     */
    protected $pid = 0;

    /**
     * @var bool
     */
    protected $alive = false;

    /**
     * @var int
     */
    protected $status = 0;

    /**
     * @param Runnable $runnable
     */
    public function __construct(Runnable $runnable = null)
    {
        if (!is_null($runnable)) {
            $this->runnable = $runnable;
        }
        $this->signal();
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

    /**
     * get pid
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return $this->alive;
    }

    /**
     * set the process stopped flag
     */
    public function setStop()
    {
        $this->alive = false;
    }

    /**
     * @return int
     */
    public function exitCode()
    {
        return $this->status;
    }

    /**
     * @return string pid
     */
    public function start()
    {
        if ($this->isAlive()) {
            throw new \LogicException("the process is already running");
        }

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
            $this->alive = true;
        } else {
            $this->signal();
            call_user_func($callback);
            exit(0);
        }
    }

    /**
     * reload process to avoid memory leaks and overloading the php script file
     */
    public function reload()
    {
        $this->stop();
        $this->start();
    }

    /**
     * kill self
     */
    public function stop()
    {
        if (!posix_kill($this->pid, SIGTERM)) {
            throw new \RuntimeException("kill son process failed");
        }

        if (pcntl_waitpid($this->pid, $this->status) == -1) {
            throw new \RuntimeException("wait son process failed");
        }
        $this->setStop();
    }

    /**
     * register signal handler
     */
    public function signal()
    {
        pcntl_signal(SIGTERM, function () {
            if ($this->beforeExit()) {
                exit(0);
            }
        });
    }

    /**
     * @param bool|true $block
     * @param int $sleep
     */
    public function wait($block = true, $sleep = 100)
    {
        while (true) {
            $res = pcntl_waitpid($this->getPid(), $status, WNOHANG);
            if ($res == 0) {
                $this->setStop();
                return;
            }
            if (!$block) {
                break;
            }
            usleep($sleep);
        }
    }

    /**
     * you should overwrite this function if you do not use the Runnable.
     */
    public function run()
    {
    }

    /**
     * when the manager process call stop function.
     * beforeExit() will web called before the sub process exit
     * if return true, the sub process will exit.
     * else it the sub process will keep running
     * @return boolean
     */
    public function beforeExit()
    {
        if (is_object($this->runnable) && method_exists($this->runnable, 'beforeExit')) {
            return call_user_func(array($this->runnable, 'beforeExit'));
        }

        return true;
    }
}