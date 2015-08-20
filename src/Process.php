<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:25
 */


namespace Jenner\SimpleFork;

class Process extends Execution
{

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
    protected $alive = false;

    /**
     * @var int
     */
    protected $status = 0;

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
     * @param $execution
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

            if(array_key_exists(self::BEFORE_START, $this->callbacks)){
                $result = call_user_func($this->callbacks[self::BEFORE_START]);
                if($result !== true){
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
            if(!array_key_exists(self::BEFORE_EXIT, $this->callbacks)){
                exit(0);
            }

            $result = call_user_func($this->callbacks[self::BEFORE_EXIT]);
            if($result === true){
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
            if ($res !== 0) {
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
     * 获取子进程执行入口
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
        if(!is_callable($function)){
            throw new \LogicException("the callback function is not callable");
        }

        $this->callbacks[$event] = $function;
    }
}