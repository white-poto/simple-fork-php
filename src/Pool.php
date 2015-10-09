<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 17:54
 */

namespace Jenner\SimpleFork;


class Pool
{
    /**
     * process list
     * @var array
     */
    protected $processes = array();

    /**
     * add a process
     * @param Process $process
     * @return int
     */
    public function submit(Process $process)
    {
        return array_push($this->processes, $process);
    }

    /**
     * start all processes
     */
    public function start()
    {
        foreach ($this->processes as $process) {
            $process->start();
        }
    }

    /**
     * shutdown all process
     */
    public function shutdown()
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->shutdown();
            }
        }
    }

    /**
     * get the count of running processes
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
     * waiting for the sub processes to exit
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
     * get process by pid
     * @param $pid
     * @return null|Process
     */
    public function getProcessByPid($pid)
    {
        foreach ($this->processes as $process) {
            if ($process->getPid() == $pid) {
                return $process;
            }
        }

        return null;
    }

    /**
     * restart
     */
    public function reload()
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->reload();
            }
        }
    }

}