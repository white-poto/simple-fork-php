SimpleFork
===================

[![Join the chat at https://gitter.im/huyanping/simple-fork-php](https://badges.gitter.im/huyanping/simple-fork-php.svg)](https://gitter.im/huyanping/simple-fork-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Latest Stable Version](https://poser.pugx.org/jenner/simple_fork/v/stable)](https://packagist.org/packages/jenner/simple_fork) 
[![Total Downloads](https://poser.pugx.org/jenner/simple_fork/downloads)](https://packagist.org/packages/jenner/simple_fork) 
[![Latest Unstable Version](https://poser.pugx.org/jenner/simple_fork/v/unstable)](https://packagist.org/packages/jenner/simple_fork) 
[![License](https://poser.pugx.org/jenner/simple_fork/license)](https://packagist.org/packages/jenner/simple_fork) 
[![travis](https://travis-ci.org/huyanping/simple-fork-php.svg)](https://travis-ci.org/huyanping/simple-fork-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/huyanping/simple-fork-php/?branch=master)

[中文README.MD](https://github.com/huyanping/simple-fork-php/blob/master/README.ZH.MD)  
Simple Fork Framework is based on PCNTL extension, the interfaces are like `Thread` and `Runnable` in Java.

Why SimpleFork
------------------------
Writing Multi-Processes programs are hard for freshman. You must consider that how to recover zombie processes, interprocess communication, especially handle the process signal.
SimpleFork framework provide several interfaces which like Java `Thread` and solutions in process pool, sync and IPC. You do not need to care about how to control multi-processes.

Require
---------------------
```bash
composer require jenner/simple_fork
```
Or
```php
require '/path/to/simple-fork-php/autoload.php'
```

Dependencies
----------------------
must  
+ php > 5.3.0
+ ext-pcntl process control 

optional
+ ext-sysvmsg message queue
+ ext-sysvsem semaphore
+ ext-sysvshm shared memory
+ ext-redis redis cache and redis message queue

Property
---------------------------
+ Process Pool and Fixed Pool
+ Recover zombie process automatically
+ shared memory, system v message queue, semaphore lock, file lock, 
redis cache, redis queue
+ Three ways to make Process: extends Process, implements Runnable or 
create a process object with a callback function
+ You can get the status of sub process
+ You can stop any processes if you want, or just shutdown all processes
+ You can reload the processes by reload() method, then the processes 
will exit and start new processes instead.

Process Pool
----------------------------------
There are two pool you can use when you have more than one process or 
task to manage:Pool and FixedPool.
+ Pool: you can execute different processes in one Pool object. 
and call the `wait` method to wait for all the sub processes exiting
(or just do something else, but do not forget to call the `wait` method)
+ ParallelPool: it will keep the sub processes count, you should not init any
socket connection before the FixedPool start(share socket connection is dangerous
in multi processes).This class has a method `reload` which can reload 
all the sub processes. When you call `reload` method, the master will 
start new N processes and shutdown the old ones.
+ SinglePool: no matter how many processes you execute, it will always keep one
process starting and start another after it stopped.
+ FixedPool: no matter how many processes you execute, it will always keep N
processes starting and start another after it stopped. the active processes'
count is less then N+1 forever.

Notice
--------------------------
+ Remember that you should call the `Process::dispatchSignal` method to call
call signal handlers for pending signals.
+ It is not recommend that adding `declare(ticks=n);` at the start of program
to handle the pending signals.
+ A better way to handle the single is that calling `pcntl_signal_dispatch` 
instead of `declare` which is more is a waste of CPU resources
+ If the sub processes exit continually and quickly, you should set `n` to 
a small integer, else set a big one to save the CPU time.
+ If you want to register signal handler in the master process, the child 
will inherit the handler.
+ If you want to register signal handler in the child process before it start, 
you can call the `Process::registerSignalHandler` method. `start` 
method of the sub process is called, it will register the signal 
handler automatically.

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
$pool->execute($producer);
$pool->execute($worker);
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
            $queue->put($i);
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
            $res = $queue->get();
            echo getmypid() . ' = ' . $i . PHP_EOL;
            var_dump($res);
        }
    }
}

$producer = new Producer();

$worker = new Worker();

$pool = new \Jenner\SimpleFork\Pool();
$pool->execute($producer);
$pool->execute($worker);
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
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->wait();
```

**Process pool to manage processes**
```php
$pool = new \Jenner\SimpleFork\Pool();
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->wait();
```

**ParallelPool to manage processes**
```php
$fixed_pool = new \Jenner\SimpleFork\ParallelPool(new TestRunnable(), 10);
$fixed_pool->start();
$fixed_pool->keep(true);
```

**FixedPool to manage processes**
```php
$pool = new \Jenner\SimpleFork\FixedPool(2);
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->wait();
```

**SinglePool to manage processes**
```php
$pool = new \Jenner\SimpleFork\SinglePool();
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));
$pool->execute(new \Jenner\SimpleFork\Process(new TestRunnable()));

$pool->wait();
```