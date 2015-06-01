<?php
namespace Jack\Symfony;

use Symfony\Component\Process\Process;

class ProcessManager {

  public $processes;

  public function runParallel(array $processes, $max_parallel, $poll = 1000)
  {
   $this->processes = $processes;

   if ( !count( $processes ) ) {
      throw new \Exception( "Can not run in parallel 0 commands" );
   }

   $this->validateProcesses($processes);
   $max_parallel = $this->fixMaxParallel($processes, $max_parallel);

   $queue = array_chunk($processes, $max_parallel);

   foreach ($queue as $process_batch) {
     $this->startChildren($process_batch, $max_parallel);
     do {
       usleep($poll);
     } while ($this->waitFor($process_batch));
   }
  }

  public function validateProcesses($processes)
  {
    foreach ($processes as $process) {
      if (!($process instanceof Process)) {
        throw new \Exception("Process in array need to be instance of Symfony Process");
      }
    }
  }

  public function fixMaxParallel($processes, $max_parallel)
  {
    $num_processes = count($processes);
    if ($max_parallel <= 0 || $max_parallel > $num_processes) {
      $max_parallel = $num_processes;
    }
    return $max_parallel;
  }

  public function startChildren(array $processes, $max_parallel)
  {
    $started = 0;
    for ($i=0; $i < $max_parallel; $i++) {
      $processes[$i]->start;
      $started++;
    }
    return $started++;
  }

  public function waitFor(array $processes)
  {
    $num_running_task = 0;
    foreach ($processes as $process) {
      if ($process->isRunning()) {
        $num_running_task++;
      }
    }
    return $num_running_task;
  }

  public function isProcessesRunning()
  {
    $running = false;
    foreach ($this->processes as $process) {
      if ($process->isRunning()) {
        $running = true;
        $break;
      }
    }
    return $running;
  }
}

/* End of file ProcessManager.php */
