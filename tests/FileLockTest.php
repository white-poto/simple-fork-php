<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 14:56
 */
class FileLockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Jenner\SimpleFork\Lock\FileLock
     */
    protected $lock;

    public function setUp(){
        $this->lock = \Jenner\SimpleFork\Lock\FileLock::create(__FILE__);
    }

    public function testLock(){
        $this->assertTrue($this->lock->acquire());
        $this->assertFalse($this->lock->acquire());
        $this->assertTrue($this->lock->release());
    }
}