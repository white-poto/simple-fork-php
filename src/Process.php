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
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var CacheInterface
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
     * @var string custom process name
     */
    protected $name = null;

    /**
     * @var bool
     */
    protected $running = null;

    /**
     * @var int process exit status
     */
    protected $exit_code = null;

    /**
     * @var int the signal which made the process terminate
     */
    protected $term_signal = null;

    /**
     * @var int the signal which made the process stop
     */
    protected $stop_signal = null;

    /**
     * @var int error code
     */
    protected $errno = null;

    /**
     * @var string error message
     */
    protected $errmsg = null;

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var array signal handlers
     */
    protected $signal_handlers = array();

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
    public function __construct($execution = null, $name = null)
    {
        if (!is_null($execution) && $execution instanceof Runnable) {
            $this->runnable = $execution;
        }
        if (!is_null($execution) && is_callable($execution)) {
            $this->execution = $execution;
        }
        if (!is_null($name)) {
            $this->name = $name;
        }

        $this->initStatus();
    }

    /**
     * init process status
     */
    protected function initStatus()
    {
        $this->pid = null;
        $this->running = null;
        $this->exit_code = null;
        $this->term_signal = null;
        $this->stop_signal = null;
        $this->errno = null;
        $this->errmsg = null;
    }

    /**
     * set or get cache
     *
     * @param CacheInterface|null $cache
     * @return bool|CacheInterface
     */
    public function cache(CacheInterface $cache = null)
    {
        // set cache
        if (!is_null($cache)) {
            $this->cache = $cache;
            return true;
        }

        // get cache
        if (is_object($this->cache) && $this->cache instanceof CacheInterface) {
            return $this->cache;
        }

        return false;
    }


    /**
     * set or get queue
     *
     * @param QueueInterface|null $queue
     * @return bool|QueueInterface
     */
    public function queue(QueueInterface $queue = null)
    {
        // set queue
        if (!is_null($queue)) {
            $this->queue = $queue;
            return true;
        }

        // get queue
        if (is_object($this->queue) && $this->queue instanceof QueueInterface) {
            return $this->queue;
        }

        return false;
    }

    /**
     * get pid
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * get or set name
     *
     * @param string|null $name
     * @return mixed
     */
    public function name($name = null)
    {
        if (!is_null($name)) {
            $this->name = $name;
        } else {
            return $this->name();
        }
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
     * get process exit code
     *
     * @return int
     */
    public function exitCode()
    {
        return $this->exit_code;
    }

    /**
     * get pcntl errno
     *
     * @return int
     */
    public function errno()
    {
        return $this->errno;
    }

    /**
     * get pcntl errmsg
     *
     * @return string
     */
    public function errmsg()
    {
        return $this->errmsg;
    }

    /**
     * start the sub process
     * and run the callback
     *
     * @return string pid
     */
    public function start()
    {
        if (!empty($this->pid) && $this->isRunning()) {
            throw new \LogicException("the process is already running");
        }

        $callback = $this->getCallback();

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        } elseif ($pid > 0) {
            $this->pid = $pid;
            $this->running = true;
        } else {
            $this->pid = getmypid();
            $this->signal();
            foreach ($this->signal_handlers as $signal => $handler) {
                pcntl_signal($signal, $handler);
            }

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
     * kill self
     *
     * @param bool|true $block
     * @param int $signal
     */
    public function shutdown($block = true, $signal = SIGTERM)
    {
        if (empty($this->pid)) {
            $message = "the process pid is null, so maybe the process is not started";
            throw new \LogicException($message);
        }
        if (!$this->isRunning()) {
            throw new \LogicException("the process is not running");
        }
        if (!posix_kill($this->pid, $signal)) {
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
     *
     * @param bool $block
     */
    public function updateStatus($block = false)
    {
        if ($this->running !== true) {
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
     * register callback functions
     *
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

    /**
     * @param $signal
     * @param callable $handler
     */
    public function registerSignalHandler($signal, callable $handler)
    {
        $this->signal_handlers[$signal] = $handler;
    }

    /**
     * you should overwrite this function
     * if you do not use the Runnable or callback.
     */
    public function run()
    {
    }

    /**
     * get sub process callback
     *
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
}