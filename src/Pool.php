<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 17:54
 */

namespace Jenner\SimpleFork;


/**
 * pool
 *
 * @package Jenner\SimpleFork
 */
class Pool extends AbstractPool
{

    /**
     * add a process
     *
     * @param Process $process
     * @param null|string $name process name
     * @return int
     */
    public function execute(Process $process, $name = null)
    {
        if (!is_null($name)) {
            $process->name($name);
        }
        $process->start();

        return array_push($this->processes, $process);
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