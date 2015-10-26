<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/26
 * Time: 14:50
 */
class RedisCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Jenner\SimpleFork\Cache\RedisCache
     */
    protected $cache;

    public function setUp()
    {
        $this->cache = new Jenner\SimpleFork\Cache\RedisCache();
    }

    public function testAll()
    {
        $this->cache->set('cache', 'test');
        $this->assertTrue($this->cache->has('cache'));
        $this->assertEquals($this->cache->get('cache'), 'test');
        $this->assertTrue($this->cache->delete('cache'));
        $this->assertNull($this->cache->get('cache'));
    }

    public function tearDown(){
        $this->cache->close();
    }
}