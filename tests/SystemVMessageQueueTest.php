<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 17:24
 */
class SystemVMessageQueueTest extends PHPUnit_Framework_TestCase
{
//    public function testAll()
//    {
//        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
//        $this->assertTrue($queue->put(1, 'test'));
//        $this->assertEquals($queue->size(1), 1);
//        $this->assertEquals($queue->get(1), 'test');
//        unset($queue);
//    }

    public function testCommunication()
    {
        $process = new \Jenner\SimpleFork\Process(function () {
            $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
            $queue->put('test');
        });
        $process->start();
        $process->wait();
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
        $this->assertEquals($queue->size(), 1);
        $this->assertEquals($queue->get(), 'test');
    }
}