<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 15:06
 */
class SemaphoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Jenner\SimpleFork\Lock\Semaphore
     */
    protected $lock;

    public function setUp(){
        $this->lock = \Jenner\SimpleFork\Lock\Semaphore::create("test");
    }


}