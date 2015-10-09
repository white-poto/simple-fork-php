<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:25
 */

namespace Jenner\SimpleFork;

use Jenner\SimpleFork\Cache\CacheInterface;
use Jenner\SimpleFork\Queue\QueueInterface;

class Process
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
     * @var Runnable
     */
    protected $runnable;

    /**
     * @var callback function
     */
    protected $execution;

    /**
     * @var int
     */
    protected $pid = 0;

    /**
     * @var bool
     */
    protected $running = null;

    /**
     * @var int process exit status
     */
    protected $exit_code = 0;

    /**
     * @var int the signal which made the process terminate
     */
    protected $term_signal;

    /**
     * @var int the signal which made the process stop
     */
    protected $stop_signal;

    /**
     * @var int error code
     */
    protected $errno;

    /**
     * @var string error message
     */
    protected $errmsg;


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
     * @param string $execution it can be a Runnable object, callback function or null
     */
    public function __construct($execution = null)
    {
        $this->signal();
        if (!is_null($execution) && $execution instanceof Runnable) {
            $this->runnable = $execution;
        }
        if (!is_null($execution) && is_callable($execution)) {
            $this->execution = $execution;
        }
    }

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
    public function isRunning()
    {
        $this->updateStatus();
        return $this->running;
    }

    /**
     * @return int get process exit code
     */
    public function exitCode()
    {
        return $this->exit_code;
    }

    /**
     * @return int get pcntl errno
     */
    public function errno()
    {
        return $this->errno();
    }

    /**
     * @return string get pcntl errmsg
     */
    public function errmsg()
    {
        return $this->errmsg;
    }

    /**
     * @return string pid
     */
    public function start()
    {
        if ($this->running === false) {
            throw new \LogicException("the process can not start more than twice");
        }

        if (!empty($this->pid) && $this->isRunning()) {
            throw new \LogicException("the process is already running");
        }

        $callback = $this->getCallback();

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        } elseif ($pid > 0) {
            $this->pid = $pid;
            $this->alive = true;
        } else {
            $this->pid = getmypid();
            $this->signal();

            if (array_key_exists(self::BEFORE_START, $this->callbacks)) {
                $result = call_user_func($this->callbacks[self::BEFORE_START]);
                if ($result !== true) {
                    exit(0);
                }
            }

            call_user_func($callback);
            exit(0);
        }
    }

    /**
     * reload process to avoid memory leaks and overloading the php script file
     */
    public function reload()
    {
        $this->shutdown(true);
        $this->start();
    }

    /**
     * kill self
     * @param bool|true $block
     */
    public function shutdown($block = true)
    {
        if (empty($this->pid)) {
            $message = "the process pid is null, so maybe the process is not started";
            throw new \LogicException($message);
        }
        if (!$this->isRunning()) {
            throw new \LogicException("the process is not running");
        }
        if (!posix_kill($this->pid, SIGTERM)) {
            throw new \RuntimeException("kill son process failed");
        }

        $this->updateStatus($block);
    }

    /**
     * register signal handler
     */
    public function signal()
    {
        pcntl_signal(SIGTERM, function () {
            if (!array_key_exists(self::BEFORE_EXIT, $this->callbacks)) {
                exit(0);
            }

            $result = call_user_func($this->callbacks[self::BEFORE_EXIT]);
            if ($result === true) {
                exit(0);
            }
        });
    }

    /**
     * update the process status
     * @param bool $block
     */
    public function updateStatus($block = false)
    {
        if (empty($this->pid)) {
            $message = "the process pid is null, so maybe the process is not started";
            throw new \RuntimeException($message);
        }

        if ($this->running === false) {
            return;
        }

        if ($block) {
            $res = pcntl_waitpid($this->pid, $status);
        } else {
            $res = pcntl_waitpid($this->pid, $status, WNOHANG | WUNTRACED);
        }

        if ($res === -1) {
            $message = "pcntl_waitpid failed. the process maybe available";
            throw new \RuntimeException($message);
        } elseif ($res === 0) {
            $this->running = true;
        } else {
            if (pcntl_wifsignaled($status)) {
                $this->term_signal = pcntl_wtermsig($status);
            }
            if (pcntl_wifstopped($status)) {
                $this->stop_signal = pcntl_wstopsig($status);
            }
            if (pcntl_wifexited($status)) {
                $this->exit_code = pcntl_wexitstatus($status);
            } else {
                $this->errno = pcntl_get_last_error();
                $this->errmsg = pcntl_strerror($this->errno);
            }

            $this->running = false;
        }
    }

    /**
     * @param bool|true $block
     * @param int $sleep
     */
    public function wait($block = true, $sleep = 100)
    {
        while (true) {
            if ($this->isRunning() === false) {
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
     * get sub process callback
     * @return array|callable|null
     */
    protected function getCallback()
    {
        $callback = null;
        if (is_object($this->runnable)) {
            $callback = array($this->runnable, 'run');
        } elseif (is_callable($this->execution)) {
            $callback = $this->execution;
        } else {
            $callback = array($this, 'run');
        }

        return $callback;
    }

    /**
     * register callback functions
     * @param $event
     * @param $function
     */
    public function on($event, $function)
    {
        if (!is_callable($function)) {
            throw new \LogicException("the callback function is not callable");
        }

        $this->callbacks[$event] = $function;
    }
}