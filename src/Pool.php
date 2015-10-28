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
     *
     * @var Process[]
     */
    protected $processes = array();

    /**
     * add a process
     *
     * @param Process $process
     * @param null|string $name process name
     * @return int
     */
    public function submit(Process $process, $name = null)
    {
        if (!is_null($name)) {
            $process->name($name);
        }
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
     *
     * @param int $signal
     */
    public function shutdown($signal = SIGTERM)
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->shutdown(true, $signal);
            }
        }
    }

    /**
     * shutdown sub process and no wait. it is dangerous,
     * maybe the sub process is working.
     */
    public function shutdownForce()
    {
        $this->shutdown(SIGKILL);
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
     * get process by pid
     *
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
     * get process by name
     *
     * @param $name process name
     * @return Process|null
     */
    public function getProcessByName($name)
    {
        foreach ($this->processes as $process) {
            if ($process->name() == $name) {
                return $process;
            }
        }

        return null;
    }
}