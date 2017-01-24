<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:25
 */

namespace Jenner\SimpleFork;

class Process
{
    /**
     * @var Runnable|callable
     */
    protected $runnable;

    /**
     * @var int
     */
    protected $pid = 0;

    /**
     * @var string custom process name
     */
    protected $name = null;

    /**
     * @var bool if the process is started
     */
    protected $started = false;

    /**
     * @var bool
     */
    protected $running = false;

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
     * @var bool
     */
    protected $if_signal = false;

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var array signal handlers
     */
    protected $signal_handlers = array();


    /**
     * @param string $execution it can be a Runnable object, callback function or null
     * @param null $name process name,you can manager the process by it's name.
     */
    public function __construct($execution = null, $name = null)
    {
        if (!is_null($execution) && $execution instanceof Runnable) {
            $this->runnable = $execution;
        } elseif (!is_null($execution) && is_callable($execution)) {
            $this->runnable = $execution;
        } elseif (!is_null($execution)) {
            throw new \InvalidArgumentException('param execution is not a object of Runnable or callable');
        } else {
            Utils::checkOverwriteRunMethod(get_class($this));
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
        $this->term_signal = null;
        $this->stop_signal = null;
        $this->errno = null;
        $this->errmsg = null;
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
            return $this->name;
        }
    }

    /**
     * if the process is stopped
     *
     * @return bool
     */
    public function isStopped()
    {
        if (is_null($this->errno)) {
            return false;
        }

        return true;
    }

    /**
     * if the process is started
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
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

    public function ifSignal()
    {
        return $this->if_signal;
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

        $callback = $this->getCallable();

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        } elseif ($pid > 0) {
            $this->pid = $pid;
            $this->running = true;
            $this->started = true;
        } else {
            $this->pid = getmypid();
            $this->signal();
            foreach ($this->signal_handlers as $signal => $handler) {
                pcntl_signal($signal, $handler);
            }
            call_user_func($callback);
            exit(0);
        }
    }

    /**
     * if the process is running
     *
     * @return bool
     */
    public function isRunning()
    {
        $this->updateStatus();
        return $this->running;
    }

    /**
     * update the process status
     *
     * @param bool $block
     */
    protected function updateStatus($block = false)
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
            throw new \RuntimeException('pcntl_waitpid failed. the process maybe available');
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
                $this->errno = pcntl_wexitstatus($status);
                $this->errmsg = pcntl_strerror($this->errno);
            } else {
                $this->errno = pcntl_get_last_error();
                $this->errmsg = pcntl_strerror($this->errno);
            }
            if (pcntl_wifsignaled($status)) {
                $this->if_signal = true;
            } else {
                $this->if_signal = false;
            }

            $this->running = false;
        }
    }

    /**
     * get sub process callback
     *
     * @return array|callable|null
     */
    protected function getCallable()
    {
        $callback = null;
        if (is_object($this->runnable) && $this->runnable instanceof Runnable) {
            $callback = array($this->runnable, 'run');
        } elseif (is_callable($this->runnable)) {
            $callback = $this->runnable;
        } else {
            $callback = array($this, 'run');
        }

        return $callback;
    }

    /**
     * register signal SIGTERM handler,
     * when the parent process call shutdown and use the default signal,
     * this handler will be triggered
     */
    protected function signal()
    {
        pcntl_signal(SIGTERM, function () {
            exit(0);
        });
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
            throw new \LogicException('the process pid is null, so maybe the process is not started');
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
     * waiting for the sub process exit
     *
     * @param bool|true $block if block the process
     * @param int $sleep default 0.1s check sub process status
     * every $sleep milliseconds.
     */
    public function wait($block = true, $sleep = 100000)
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
     * register sub process signal handler,
     * when the sub process start, the handlers will be registered
     *
     * @param $signal
     * @param callable $handler
     */
    public function registerSignalHandler($signal, callable $handler)
    {
        $this->signal_handlers[$signal] = $handler;
    }

    /**
     * after php-5.3.0, we can call pcntl_singal_dispatch to call signal handlers for pending signals
     * which can save cpu resources than using declare(tick=n)
     *
     * @return bool
     */
    public function dispatchSignal()
    {
        return pcntl_signal_dispatch();
    }

    /**
     * you should overwrite this function
     * if you do not use the Runnable or callback.
     */
    public function run()
    {
    }
}