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
        $this->queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        $this->assertTrue($this->queue->put('test', 'test'));
        $this->assertEquals($this->queue->get('test'), 'test');
        $this->assertEquals($this->queue->size('test'), 0);
        $this->queue->close();
    }

    public function testCommunication()
    {
        $process = new \Jenner\SimpleFork\Process(function () {
            $queue = new \Jenner\SimpleFork\Queue\RedisQueue();
            $queue->put('test', 'test');
            $queue->close();
        });
        $process->start();
        $process->wait();
        $queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        $this->assertEquals($queue->size('test'), 1);
        $this->assertEquals($queue->get('test'), 'test');
        $queue->close();
    }

}