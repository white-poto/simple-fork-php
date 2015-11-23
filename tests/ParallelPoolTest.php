<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/2
 * Time: 18:06
 */
class ParallelPoolTest extends PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $pool = new \Jenner\SimpleFork\ParallelPool(new ParallelPoolTestRunnable(), 10);
        $pool->start();
        $this->assertEquals(10, $pool->aliveCount());
        sleep(4);
        $this->assertEquals(0, $pool->aliveCount());
        $pool->keep();
        $this->assertEquals(10, $pool->count());
        $this->assertEquals(10, $pool->aliveCount());
        $pool->wait(true);
    }

    public function testException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $pool = new \Jenner\SimpleFork\ParallelPool('test');
    }

    public function testReload()
    {
        $pool = new \Jenner\SimpleFork\ParallelPool(new ParallelPoolTestRunnable(), 10);
        $pool->start();
        $this->assertEquals(10, $pool->aliveCount());
        $old_processes = $pool->getProcesses();
        $pool->reload();
        $new_processes = $pool->getProcesses();
        foreach ($old_processes as $old_process) {
            foreach ($new_processes as $new_process) {
                $this->assertTrue($old_process->getPid() != $new_process->getPid());
            }
        }
        $pool->shutdown();
    }
}


class ParallelPoolTestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * process entry
     *
     * @return mixed
     */
    public function run()
    {
        sleep(3);
    }
}