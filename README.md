# symfony-parallel-process

A simple wrapper to enable parallel processing using [Symfony Process](http://symfony.com/doc/current/components/process.html) component.

Example

```php
<?php

use Symfony\Component\Process\Process;
use Jack\Symfony\ProcessManager;

$proc1 = new Process('ls -l');
$proc2 = new Process('ls -l');

$proc_mgr = new ProcessManager();

$processes = array();
array_push($processes, $proc1, $proc2);

$max_parallel_processes = 5;
$polling_interval = 1000; // microseconds
$proc_mgr->runParallel($processes, $max_parallel_processes, $polling_interval);
```
