<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:00
 */

namespace Jenner\SimpleFork\Cache;


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
     */
    public function __construct($size = 33554432)
    {
        $this->size = $size;
        if (function_exists("shm_attach") === false) {
            $message = "\nYour PHP configuration needs adjustment. " .
                "See: http://us2.php.net/manual/en/shmop.setup.php. " .
                "To enable the System V shared memory support compile " .
                " PHP with the option --enable-sysvshm.";

            throw new \RuntimeException($message);
        }
        $this->attach(); //create resources (shared memory)
    }

    /**
     * init shared memory
     */
    public function attach()
    {
        //增加客户端连接数
        $tmp_file = '/tmp/' . basename(__FILE__);
        touch($tmp_file);
        $key = ftok($tmp_file, 'a');
        $this->shm = shm_attach($key, $this->size); //allocate shared memory
        $this->set($this->client_count_key, $this->get($this->client_count_key) + 1);
    }

    /**
     * @return bool
     */
    public function dettach()
    {
        $this->set($this->client_count_key, $this->get($this->client_count_key) - 1);
        //如果是最后一个使用的客户端，则删除共享内存
        if ($this->get($this->client_count_key) == 0) {
            return $this->remove();
        }
        return shm_detach($this->shm); //allocate shared memory
    }

    /**
     * remove shared memory
     * @return bool
     */
    public function remove()
    {
        //dallocate shared memory
        return shm_remove($this->shm);
    }

    /**
     * set var
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value)
    {
        return shm_put_var($this->shm, $this->shm_key($key), $value); //store var
    }

    /**
     * get var
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
     * delete var
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
     * has var ?
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
     * generate shm key
     * @param $val
     * @return mixed
     */
    public function shm_key($val)
    { // enable all world langs and chars !
        return preg_replace("/[^0-9]/", "", (preg_replace("/[^0-9]/", "", md5($val)) / 35676248) / 619876); // text to number system.
    }

    /**
     * init when wakeup
     */
    public function __wakeup()
    {
        $this->attach();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->dettach();
        unset($this);
    }
}