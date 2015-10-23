<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 16:58
 */
class PoolTest extends PHPUnit_Framework_TestCase
{
    public function testAliveCount(){
        $pool = new \Jenner\SimpleFork\Pool();
        for($i=0; $i<10; $i++){
            $process = new \Jenner\SimpleFork\Process(function(){
                sleep(10);
            });
            $pool->submit($process);
        }
        $pool->start();
        $this->assertEquals(10, $pool->aliveCount());
        $pool->wait();
    }
}