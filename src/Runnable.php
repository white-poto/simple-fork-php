<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:28
 */

namespace Jenner\SimpleFork;


interface Runnable
{
    /**
     * process entry
     *
     * @return mixed
     */
    public function run();
}