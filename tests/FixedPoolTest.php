<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/23 15:04
 */

class FixedPoolTest extends PHPUnit_Framework_TestCase
{
    public function testAliveCount(){
        $pool = \Jenner\SimpleFork\PoolFactory::newFixedPool(4);
        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());

        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());
        $pool->execute(new FixedPoolTestProcess());

        $this->assertEquals(4, $pool->aliveCount());
        sleep(4);
        $pool->wait();
        $this->assertEquals(4, $pool->aliveCount());
        $pool->wait(true);
        $this->assertEquals(0, $pool->aliveCount());
    }
}


class FixedPoolTestProcess extends \Jenner\SimpleFork\Process
{

    /**
     * process entry
     *
     * @return mixed
     */
    public function run()
    {
        sleep(3);
    }
}