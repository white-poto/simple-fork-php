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

    public function testAll()
    {
        if(!extension_loaded("Redis")){
            $this->markTestSkipped("Redis extension is not loaded");
        }
        $this->cache = new Jenner\SimpleFork\Cache\RedisCache();
        $this->cache->set('cache', 'test');
        $this->assertTrue($this->cache->has('cache'));
        $this->assertEquals($this->cache->get('cache'), 'test');
        $this->assertTrue($this->cache->delete('cache'));
        $this->assertNull($this->cache->get('cache'));
        $this->cache->close();
    }

}