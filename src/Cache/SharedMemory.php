<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:00
 */

namespace Jenner\SimpleFork\Cache;


/**
 * shared memory cache
 *
 * @package Jenner\SimpleFork\Cache
 */
class SharedMemory implements CacheInterface
{
    /**
     * holds shared memory resource
     * @var resource
     */
    protected $shm;

    /**
     * shared memory ipc key
     * @var string
     */
    protected $client_count_key = 'system_client_count';

    /**
     * memory size
     * @var int
     */
    protected $size;

    /**
     * @param int $size memory size
     * @param string $file
     */
    public function __construct($size = 33554432, $file = __FILE__)
    {
        $this->size = $size;
        if (function_exists("shm_attach") === false) {
            $message = "\nYour PHP configuration needs adjustment. " .
                "See: http://us2.php.net/manual/en/shmop.setup.php. " .
                "To enable the System V shared memory support compile " .
                " PHP with the option --enable-sysvshm.";

            throw new \RuntimeException($message);
        }
        $this->attach($file); //create resources (shared memory)
    }

    /**
     * connect shared memory
     *
     * @param string $file
     */
    public function attach($file = __FILE__)
    {
        if (!file_exists($file)) {
            $touch = touch($file);
            if (!$touch) {
                throw new \RuntimeException("file is not exists and it can not be created. file: {$file}");
            }
        }
        $key = ftok($file, 'a');
        $this->shm = shm_attach($key, $this->size); //allocate shared memory
    }

    /**
     * remove shared memory.
     * you should know that it maybe does not work.
     *
     * @return bool
     */
    public function remove()
    {
        //dallocate shared memory
        if (!shm_remove($this->shm)) {
            return false;
        }
        $this->dettach();
        // shm_remove maybe not working. it likes a php bug.
        unset($this->shm);

        return true;
    }

    /**
     * @return bool
     */
    public function dettach()
    {
        return shm_detach($this->shm); //allocate shared memory
    }

    /**
     * set var
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value)
    {
        return shm_put_var($this->shm, $this->shm_key($key), $value); //store var
    }

    /**
     * generate shm key
     *
     * @param $val
     * @return mixed
     */
    public function shm_key($val)
    {   // enable all world langs and chars !
        // text to number system.
        return preg_replace("/[^0-9]/", "", (preg_replace("/[^0-9]/", "", md5($val)) / 35676248) / 619876);
    }

    /**
     * get var
     *
     * @param $key
     * @param null $default
     * @return bool|mixed
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return shm_get_var($this->shm, $this->shm_key($key));
        } else {
            return $default;
        }
    }

    /**
     * has var ?
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if (shm_has_var($this->shm, $this->shm_key($key))) { // check is isset
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete var
     *
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            return shm_remove_var($this->shm, $this->shm_key($key));
        } else {
            return false;
        }
    }

    /**
     * init when wakeup
     */
    public function __wakeup()
    {
        $this->attach();
    }
}