<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 17:18
 */
class RedisQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Jenner\SimpleFork\Queue\RedisQueue
     */
    protected $queue;

    public function testAll()
    {
        if(!extension_loaded("Redis")){
            $this->markTestSkipped("Redis extension is not loaded");
        }
        $this->queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        $this->assertTrue($this->queue->put('test'));
        $this->assertEquals($this->queue->get(), 'test');
        $this->assertEquals($this->queue->size(), 0);
        $this->queue->close();
    }

    public function testCommunication()
    {
        if(!extension_loaded("Redis")){
            $this->markTestSkipped("Redis extension is not loaded");
        }
        $process = new \Jenner\SimpleFork\Process(function () {
            $queue = new \Jenner\SimpleFork\Queue\RedisQueue();
            $queue->put('test');
            $queue->close();
        });
        $process->start();
        $process->wait();
        $queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        $this->assertEquals($queue->size(), 1);
        $this->assertEquals($queue->get(), 'test');
        $queue->close();
    }

}