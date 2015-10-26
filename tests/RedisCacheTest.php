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
        $this->cache->set('test', 'test');
        $this->assertTrue($this->cache->has('test'));
        $this->assertEquals($this->cache->get('test'), 'test');
        $this->assertTrue($this->cache->delete('test'));
        $this->assertNull($this->cache->get('test'));
    }
}