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
        if (!$process->isStarted()) {
            $process->start();
        }

        return array_push($this->processes, $process);
    }
}