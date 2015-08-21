<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 20:52
 */

namespace Jenner\SimpleFork\Lock;


class Semaphore implements LockInterface
{
    /**
     * get a lock instance
     * @param $key
     * @return Semaphore
     */
    public static function create($key)
    {
        return new Semaphore($key);
    }

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
     * @param $key
     * @throws \RuntimeException
     */
    private function __construct($key)
    {
        if (($this->lock_id = sem_get($this->_stringToSemKey($key))) === false) {
            throw new \RuntimeException('Cannot create semaphore for key: ' . $key);
        }
    }

    /**
     * release lock
     * @throws \RuntimeException
     */
    public function __destruct()
    {
        $this->release();
    }


    /**
     * get a lock
     * @throws \Exception
     */
    public function acquire()
    {
        if (!sem_acquire($this->lock_id)) {
            throw new \RuntimeException('Cannot acquire semaphore: ' . $this->lock_id);
        }
        $this->locked = true;
    }

    /**
     * release lock
     * @throws \RuntimeException
     */
    public function release()
    {
        if ($this->locked) {
            if (!sem_release($this->lock_id)) {
                throw new \RuntimeException('Cannot release semaphore: ' . $this->lock_id);
            }
            $this->locked = false;
        }
    }

    /**
     * Semaphore requires a numeric value as the key
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
}