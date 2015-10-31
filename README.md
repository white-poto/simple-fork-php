SimpleFork
===================
[![Latest Stable Version](https://poser.pugx.org/jenner/simple_fork/v/stable)](https://packagist.org/packages/jenner/simple_fork) 
[![Total Downloads](https://poser.pugx.org/jenner/simple_fork/downloads)](https://packagist.org/packages/jenner/simple_fork) 
[![Latest Unstable Version](https://poser.pugx.org/jenner/simple_fork/v/unstable)](https://packagist.org/packages/jenner/simple_fork) 
[![License](https://poser.pugx.org/jenner/simple_fork/license)](https://packagist.org/packages/jenner/simple_fork) 
[![travis](https://travis-ci.org/huyanping/simple-fork-php.svg)](https://travis-ci.org/huyanping/simple-fork-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/?branch=master)

[中文README.MD](https://github.com/huyanping/simple-fork-php/blob/master/README.ZH.MD)  
Simple Fork Framework based on PCNTL, the interfaces are like Thread and Runnable in Java.

Why SimpleFork
------------------------
Writing Multi-Process program is hard for freshman. You must consider that how to recover zombie process, interprocess communication and so on. Especially handle the process signal.
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
+ ext-redis redis cache and redis message queue

Property
---------------------------
+ Process Pool
+ Recover zombie process automatically
+ shared memory, system v message queue, semaphore lock. redis cache, redis queue
+ Two ways to make Process: extends Process or implements Runnable
+ You can get the status of sub process
+ You can stop any process if you want, or just shutdown all process.
+ You can register Process::BEFORE_EXIT and Process::BEFORE_START 
callback functions by Process::on(). 
If the callback function return true, the process will exit, else it will continue to run.
+ You can reload the processes by reload() method, then the processes 
will exit and start new process instead.

Callback functions
-------------------------------
Use Process::on($event, $callback) method to register callback functions  
+ Process::BEFORE_START It will be called when the process start. 
If it return false, the process will not start and exit with status 0.
+ Process::BEFORE_EXIT It will be called when the main process call stop() method. 
If it return false, the process will not exit.

Notice
--------------------------
If you want to register signal handler in the master process, the child will inherit the handler.
If you want to register signal handler in child process but before it start, 
you can call the `Process::registerSignalHandler` method. After the child process
start, it will register the signal handler automatically.

Examples
-------------------------
More examples in [examples](https://github.com/huyanping/simple-fork-php/tree/master/examples examples) dictionary  
**A simple example.**  
```php
class TestRunnable implements \Jenner\SimpleFork\Runnable{

    /**
     * Entrance
     * @return mixed
     */
    public function run()
    {
        echo "I am a sub process" . PHP_EOL;
    }
}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();
$process->wait();
```

**A process using callback**
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

**Process communication using shared memory** 
```php
class Producer extends \Jenner\SimpleFork\Process{
    public function run(){
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();
        //$cache = new \Jenner\SimpleFork\Cache\RedisCache();
        for($i = 0; $i<10; $i++){
            $cache->set($i, $i);
            echo "set {$i} : {$i}" . PHH_EOL;
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process{
    public function run(){
        sleep(5);
        $cache = new \Jenner\SimpleFork\Cache\SharedMemory();
        //$cache = new \Jenner\SimpleFork\Cache\RedisCache();
        for($i=0; $i<10; $i++){
            echo "get {$i} : " . $cache->get($i) . PHP_EOL;
        }
    }
}

$producer = new Producer();

$worker = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();
```

**Process communication using system v message queue** 
```php
class Producer extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
        //$queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        for ($i = 0; $i < 10; $i++) {
            echo getmypid() . PHP_EOL;
            $queue->put(1, $i);
        }
    }
}

class Worker extends \Jenner\SimpleFork\Process
{
    public function run()
    {
        sleep(5);
        $queue = new \Jenner\SimpleFork\Queue\SystemVMessageQueue();
        //$queue = new \Jenner\SimpleFork\Queue\RedisQueue();
        for ($i = 0; $i < 10; $i++) {
            $res = $queue->get(1);
            echo getmypid() . ' = ' . $i . PHP_EOL;
            var_dump($res);
        }
    }
}

$producer = new Producer();

$worker = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit($producer);
$pool->submit($worker);
$pool->start();
$pool->wait();
```

**Process communication using Semaphore lock**
```php
class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * @var \Jenner\SimpleFork\Lock\LockInterface
     */
    protected $sem;

    public function __construct()
    {
        $this->sem = \Jenner\SimpleFork\Lock\Semaphore::create("test");
        //$this->sem = \Jenner\SimpleFork\Lock\FileLock::create("/tmp/test.lock");
    }

    /**
     * @return mixed
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->sem->acquire();
            echo "my turn: {$i} " . getmypid() . PHP_EOL;
            $this->sem->release();
            sleep(1);
        }
    }
}

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->start();
$pool->wait();
```

**Process pool to manage processes**
```php
class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * @return mixed
     */
    public function run()
    {
        sleep(10);
        echo getmypid() . ':done' . PHP_EOL;
    }
}

$pool = new \Jenner\SimpleFork\Pool();
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->submit(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->start();
$pool->wait();
```