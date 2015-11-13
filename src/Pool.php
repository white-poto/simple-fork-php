<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 17:54
 */

namespace Jenner\SimpleFork;


class Pool extends AbstractPool
{

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
     * get process by name
     *
     * @param string $name process name
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