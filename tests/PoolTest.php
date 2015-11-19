<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 16:58
 */
class PoolTest extends PHPUnit_Framework_TestCase
{
    public function testMethods()
    {
        $process = new \Jenner\SimpleFork\Process(function () {
            sleep(3);
        }, 'test');
        $pool = new \Jenner\SimpleFork\Pool();
        $pool->execute($process);
        $this->assertEquals(1, $pool->aliveCount());
        $this->assertEquals($process, $pool->getProcessByPid($process->getPid()));
        $this->assertEquals($process, $pool->getProcessByName('test'));
    }

    public function testAliveCount()
    {
        $pool = new \Jenner\SimpleFork\Pool();
        for ($i = 0; $i < 10; $i++) {
            $process = new \Jenner\SimpleFork\Process(function () {
                sleep(3);
            });
            $pool->execute($process);
        }
        $start = time();
        $this->assertEquals(10, $pool->aliveCount());
        $pool->wait();
        $time = time() - $start;
        $this->assertTrue($time >= 3);
        $this->assertEquals(0, $pool->aliveCount());
    }

    public function testShutdown()
    {
        $pool = new \Jenner\SimpleFork\Pool();
        for ($i = 0; $i < 10; $i++) {
            $process = new \Jenner\SimpleFork\Process(function () {
                sleep(3);
            });
            $pool->execute($process);
        }
        $start = time();
        $pool->shutdown();
        $time = time() - $start;
        $this->assertTrue($time < 3);
        $this->assertEquals(0, $pool->aliveCount());
    }

    public function testShutdownForce()
    {
        $pool = new \Jenner\SimpleFork\Pool();
        for ($i = 0; $i < 10; $i++) {
            $process = new \Jenner\SimpleFork\Process(function () {
                sleep(3);
            });
            $pool->execute($process);
        }
        $start = time();
        $pool->shutdownForce();
        $time = time() - $start;
        $this->assertTrue($time < 3);
        $this->assertEquals(0, $pool->aliveCount());
    }
}