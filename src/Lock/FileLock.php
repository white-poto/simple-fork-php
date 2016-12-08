<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/21
 * Time: 14:30
 */

namespace Jenner\SimpleFork\Lock;


/**
 * file lock
 *
 * @package Jenner\SimpleFork\Lock
 */
class FileLock implements LockInterface
{
    /**
     * @var string lock file
     */
    protected $file;

    /**
     * @var resource
     */
    protected $fp;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @param $file
     */
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
     * create a file lock instance
     * if the file is not exists, it will be created
     *
     * @param string $file lock file
     * @return FileLock
     */
    public static function create($file)
    {
        return new FileLock($file);
    }

    /**
     * get a lock
     *
     * @param bool $blocking
     * @return mixed
     */
    public function acquire($blocking = true)
    {
        if ($this->locked) {
            throw new \RuntimeException('already lock by yourself');
        }

        if ($blocking) {
            $locked = flock($this->fp, LOCK_EX);
        } else {
            $locked = flock($this->fp, LOCK_EX | LOCK_NB);
        }

        if ($locked !== true) {
            return false;
        }
        $this->locked = true;

        return true;
    }

    /**
     * is locked
     *
     * @return mixed
     */
    public function isLocked()
    {
        return $this->locked === true ? true : false;
    }

    /**
     *
     */
    public function __destory()
    {
        if ($this->locked) {
            $this->release();
        }
    }

    /**
     * release lock
     *
     * @return mixed
     */
    public function release()
    {
        if (!$this->locked) {
            throw new \RuntimeException('release a non lock');
        }

        $unlock = flock($this->fp, LOCK_UN);
        fclose($this->fp);
        if ($unlock !== true) {
            return false;
        }
        $this->locked = false;

        return true;
    }
}