SimpleFork
===================
[https://github.com/huyanping/simple-fork-php/blob/master/README.ZH.MD](https://github.com/huyanping/simple-fork-php/blob/master/README.ZH.MD 中文README.MD)  
simple fork framework based on PCNTL, the interfaces are like Thread and Runnable in Java.

Why SimpleFork
------------------------
Multi-Process program is hard for freshman. You must consider recover zombie process, interprocess communication and so on. Especially handle the process signal.
SimpleFork framework provide several interfaces which like Java and solutions in process collect, sync and IPC. You do not need to consider that how to control multi-process.

Require
---------------------
```bash
composer require jenner/simple_fork
```
```php
require path/to/SimpleFork/autoload.php
```

Dependencies
----------------------
must  
+ ext-pcntl process control 

optional
+ ext-sysvmsg message queue
+ ext-sysvsem semaphore
+ ext-sysvshm shared memory

Property
---------------------------
+ Process Pool
+ Auto recover zombie process
+ shared memory, system v message queue, semaphore lock. redis cache, redis queue
+ Two way to make Process: extends Process or implements Runnable
+ You can get the status of sub process
+ You can stop any process if you want, or just shutdown all process.
+ You can register Process::BEFORE_EXIT callback functions by Process::on(). If the callback function return true, the process will exit, else it will continue to run.
+ You can reload the processes by reload() method.

回调函数
-------------------------------
use Process::on($event, $callback) method to register callback functions  
+ Process::BEFORE_START It will be called when the process start. If it return false, the process will not start and exit with status 0.
+ Process::BEFORE_EXIT It will be called when the main process call stop() method. If it return false, the process will not exit.


Examples
-------------------------
more examples in [https://github.com/huyanping/simple-fork-php/tree/master/examples](https://github.com/huyanping/simple-fork-php/tree/master/examples examples) dictionary  
simple.php  
```php
class TestRunnable extends \Jenner\SimpleFork\Runnable{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        echo "I am a sub process" . PHP_EOL;
    }
}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();
```

callback.php  
```php
$process = new \Jenner\SimpleFork\Process(function(){
    for($i=0; $i<3; $i++){
        echo $i . PHP_EOL;
        sleep(1);
    }
});

$process->start();
$process->wait();
```

shared_memory.php
```php
class Producer extends \Jenner\SimpleFork\Process{
    public function run(){
        for($i = 0; $i<10; $i++){
            $this->cache->set($i, $i);
            echo "set {$i} : {$i}" . PHH_EOL;
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process{
    public function run(){
        sleep(5);
        for($i=0; $i<10; $i++){
            echo "get {$i} : " . $this->cache->get($i) . PHP_EOL;
        }
    }
}

$memory = new \Jenner\SimpleFork\IPC\SharedMemory();
$producer = new Producer();
$producer->setCache($memory);

$worker = new Worker();
$worker->setCache($memory);

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();
```