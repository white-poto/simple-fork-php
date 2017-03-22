<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:52
 */

namespace Jenner\SimpleFork\Lock;


/**
 * sem lock
 *
 * @package Jenner\SimpleFork\Lock
 */
class Semaphore implements LockInterface
{
    /**
     * @var
     */
    private $lock_id;
    /**
     * @var bool
     */
    private $locked = false;

    /**
     * init a lock
     *
     * @param $key
     * @param $count
     * @throws \RuntimeException
     */
    private function __construct($key, $count = 1)
    {
        if (($this->lock_id = sem_get($this->_stringToSemKey($key), $count)) === false) {
            throw new \RuntimeException("Cannot create semaphore for key: {$key}");
        }
    }

    /**
     * Semaphore requires a numeric value as the key
     *
     * @param $identifier
     * @return int
     */
    protected function _stringToSemKey($identifier)
    {
        $md5 = md5($identifier);
        $key = 0;
        for ($i = 0; $i < 32; $i++) {
            $key += ord($md5{$i}) * $i;
        }
        return $key;
    }

    /**
     * create a lock instance
     *
     * @param $key
     * @return Semaphore
     */
    public static function create($key)
    {
        return new Semaphore($key);
    }

    /**
     * release lock
     *
     * @throws \RuntimeException
     */
    public function __destruct()
    {
        if ($this->isLocked()) {
            $this->release();
        }
    }

    /**
     * is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked === true ? true : false;
    }

    /**
     * release lock
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function release()
    {
        if (!$this->locked) {
            throw new \RuntimeException("release a non lock");
        }

        if (!sem_release($this->lock_id)) {
            return false;
        }
        $this->locked = false;

        return true;
    }

    /**
     * get a lock
     *
     * @param bool $blocking
     * @return bool
     */
    public function acquire($blocking = true)
    {
        if ($this->locked) {
            throw new \RuntimeException('already lock by yourself');
        }

        if ($blocking === false) {
            if (version_compare(PHP_VERSION, '5.6.0') < 0) {
                throw new \RuntimeException('php version is at least 5.6.0 for param blocking');
            }
            if (!sem_acquire($this->lock_id, true)) {
                return false;
            }
            $this->locked = true;

            return true;
        }

        if (!sem_acquire($this->lock_id)) {
            return false;
        }
        $this->locked = true;

        return true;
    }

    /**
     * remove the semaphore resource
     *
     * @return bool
     */
    public function remove()
    {
        if ($this->locked) {
            throw new \RuntimeException('can not remove a locked semaphore resource');
        }
        if (!is_resource($this->lock_id)) {
            throw new \RuntimeException('can not remove a empty semaphore resource');
        }

        if (!sem_release($this->lock_id)) {
            return false;
        }

        return true;
    }
}