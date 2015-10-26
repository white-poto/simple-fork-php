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

    /**
     *
     */
    public function setUp(){
        $this->queue = new \Jenner\SimpleFork\Queue\RedisQueue();
    }

    public function testAll(){

    }
}