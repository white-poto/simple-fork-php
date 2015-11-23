<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/21
 * Time: 14:24
 */

namespace Jenner\SimpleFork\Lock;


/**
 * lock for processes to mutual exclusion
 *
 * @package Jenner\SimpleFork\Lock
 */
interface LockInterface
{
    /**
     * get a lock
     *
     * @param bool $blocking
     * @return bool
     */
    public function acquire($blocking = true);

    /**
     * release lock
     *
     * @return bool
     */
    public function release();

    /**
     * is locked
     *
     * @return bool
     */
    public function isLocked();
}