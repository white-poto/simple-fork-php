<?php
/**
 * @author Jenner <hypxm@qq.com>
 * @blog http://www.huyanping.cn
 * @license https://opensource.org/licenses/MIT MIT
 * @datetime: 2015/11/19 21:13
 */

namespace Jenner\SimpleFork;


class SinglePool extends FixedPool
{
    public function __construct()
    {
        parent::__construct(1);
    }
}