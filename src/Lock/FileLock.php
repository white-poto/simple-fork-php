<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/21
 * Time: 14:30
 */

namespace Jenner\SimpleFork\Lock;


class FileLock implements LockInterface
{
    protected $file;

    protected $fp;

    protected $locked = false;

    public static function create($file)
    {
        return new FileLock($file);
    }

    private function __construct($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \RuntimeException("{$file} is not exists or not readable");
        }
        $this->fp = fopen($file, "r+");
        if (!is_resource($this->fp)) {
            throw new \RuntimeException("open {$file} failed");
        }
    }

    /**
     * get a lock
     * @return mixed
     */
    public function acquire()
    {
        $locked = flock($this->fp, LOCK_EX);
        if (!$locked) {
            throw new \RuntimeException("get lock failed");
        }
        $this->locked = true;

        return true;
    }

    /**
     * release lock
     * @return mixed
     */
    public function release()
    {
        $unlock = flock($this->fp, LOCK_UN);
        if (!$unlock) {
            throw new \RuntimeException("release lock failed");
        }
        $this->locked = false;

        return true;
    }

    public function __destory()
    {
        if ($this->locked) {
            $this->release();
        }
    }
}