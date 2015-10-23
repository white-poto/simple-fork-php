<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/23
 * Time: 17:02
 */
class SharedMemoryTest extends PHPUnit_Framework_TestCase
{
    public function testSetAndGet(){
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();

        $process = new \Jenner\SimpleFork\Process(function() use($cache){
            $cache->set('test', 'test');
        });
        $process->start();
        sleep(1);
        $this->assertEquals('test', $cache->get('test'));
        $process->wait();
    }

    public function testHas(){
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();
        $cache->set('test', 'test');
        $this->assertTrue($cache->has('test'));
        $cache->delete('test');
        $this->assertFalse($cache->has('test'));
    }
}