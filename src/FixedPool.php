<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/2
 * Time: 17:45
 */

namespace Jenner\SimpleFork;


class FixedPool
{
    /**
     * @var Process[] process list
     */
    protected $processes;

    /**
     * @var callable|Runnable sub process callback
     */
    protected $runnable;

    /**
     * @var int max process count
     */
    protected $max;

    /**
     * @param callable|Runnable $callback
     * @param int $max
     */
    public function __construct($callback, $max = 10)
    {
        if (!is_callable($callback) || !($callback instanceof Runnable)) {
            $message = "callback must be a callback function or a object of Runnalbe";
            throw new \InvalidArgumentException($message);
        }

        $this->runnable;
        $this->max = $max;
        $this->processes = array();
    }

    /**
     * start the pool
     */
    public function start()
    {
        $alive_count = $this->aliveCount();
        // create sub process and run
        if ($alive_count < $this->max) {
            $need = $this->max - $alive_count;
            for ($i = 0; $i < $need; $i++) {
                $process = new Process($this->runnable);
                $process->start();
                $this->processes[$process->getPid()] = $process;
            }
        }

        // recycle sub process and delete the processes
        // which are not running from process list
        foreach ($this->processes as $process) {
            if (!$process->isRunning()) {
                unset($this->processes[$process->getPid()]);
            }
        }
    }

    /**
     * keep sub process count
     *
     * @param bool $block block the master process
     * to keep the sub process count all the time
     * @param int $interval check time interval
     */
    public function keep($block = false, $interval = 100)
    {
        do {
            $this->start();
            $block ? usleep($interval) : null;
        } while ($block);
    }

    /**
     * waiting for the sub processes to exit
     *
     * @param bool|true $block if true the parent process will be blocked until all
     * sub processes exit. else it will check if thers are processes that had been exited once and return.
     * @param int $sleep when $block is true, it will check sub processes every $sleep minute
     */
    public function wait($block = true, $sleep = 100)
    {
        do {
            foreach ($this->processes as $process) {
                if (!$process->isRunning()) {
                    continue;
                }
            }
            usleep($sleep);
        } while ($block && $this->aliveCount() > 0);
    }

    /**
     * get the count of running processes
     *
     * @return int
     */
    public function aliveCount()
    {
        $count = 0;
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * return process count
     *
     * @return int
     */
    public function count()
    {
        return count($this->processes);
    }
}