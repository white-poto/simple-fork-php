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
     * @var array
     */
    protected $processes = array();

    public function __construct()
    {
    }

    public function submit(Process $process)
    {
        return array_push($this->processes, $process);
    }

    public function start()
    {
        foreach ($this->processes as $process) {
            $process->start();
        }
    }

    public function shutdown()
    {
        foreach ($this->processes as $process) {
            if ($process->isAlive()) {
                $process->stop();
            }
        }
    }

    public function aliveCount(){
        $count = 0;
        foreach($this->processes as $process){
            if($process->isAlive()){
                $count++;
            }
        }

        return $count;
    }


    public function wait($block = true, $sleep = 100){
        do{
            foreach($this->processes as $process){
                $res = pcntl_waitpid($process->getPid(), $status, WNOHANG);
                if($res != 0){
                    $process->setStop();
                }
            }
            usleep($sleep);
        }while($block && $this->aliveCount() > 0);
    }

}