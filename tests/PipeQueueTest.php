<?php

/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/24 21:17
 */
class PipeQueueTest extends PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $queue = new \Jenner\SimpleFork\Queue\PipeQueue();
        $this->assertTrue($queue->put('test'));
        $this->assertEquals($queue->get(), 'test');
    }
}