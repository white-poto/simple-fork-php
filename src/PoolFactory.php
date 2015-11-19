<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/19 21:14
 */

namespace Jenner\SimpleFork;


class PoolFactory
{
    public static function newPool()
    {
        return new Pool();
    }

    public static function newFixedPool($max = 4)
    {
        return new FixedPool($max);
    }

    public static function newParallelPool($callback, $max = 4)
    {
        return new ParallelPool($callback, $max);
    }

    public static function newSinglePool()
    {
        return new SinglePool();
    }
}