<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2016/6/22
 * Time: 16:22
 */
class FileCacheTest extends PHPUnit_Framework_TestCase
{

    public function additionProvider()
    {
        return array(
            array("/tmp/cache1", "key1", "value1"),
            array("/tmp/cache2", "key2", "value2"),
            array("/tmp/cache3", "key3", "value3"),
            array("/tmp/cache4", "key4", "value4")
        );
    }

    /**
     * @dataProvider additionProvider
     * @param $path
     * @param $key
     * @param $value
     */
    public function testCache($path, $key, $value) {
        $cache = new \Jenner\SimpleFork\Cache\FileCache($path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue($cache->set($key, $value, 2));
        $this->assertEquals($cache->get($key), $value);
        sleep(4);
        $this->assertNull($cache->get($key));
        $this->assertTrue($cache->set($key, $value));
        $this->assertTrue($cache->delete($key));
        $this->assertNull($cache->get($key));
        $this->assertTrue($cache->flush());
        $this->assertFalse(file_exists($path));
    }
}