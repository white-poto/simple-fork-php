<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/21
 * Time: 14:24
 */

namespace Jenner\SimpleFork\Lock;


interface LockInterface
{
    /**
     * get a lock
     * @return mixed
     */
    public function acquire();

    /**
     * release lock
     * @return mixed
     */
    public function release();
}