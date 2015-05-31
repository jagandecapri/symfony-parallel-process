<?php
namespace Jack\Symfony;

use Jack\Symfony\ProcessManager;
use Symfony\Component\Process\Process;

class ProcessManagerTest extends \PHPUnit_Framework_TestCase  {

  public $process_manager;

  public function setUp()
  {
    $this->process_manager = new ProcessManager();
  }

  /**
   * @expectedException Exception
   */
  public function testRunParallelWithZeroProcesses()
  {
      $this->process_manager->runParallel(array(), 0);
  }

  /**
   * @expectedException Exception
   */
  public function testRunParallelWithNonSymfonyProcess()
  {
      $this->process_manager->runParallel(array('ls -la'), 0);
  }

  public function testRunParallel()
  {
    $processes =  array(
       new Process('echo foo'),
       new Process('echo bar')
    );
    $this->process_manager->runParallel($processes, 2, 1000);
    do{

    }while($this->process_manager->isProcessesRunning());

    $this->assertEquals("foo\n", $processes[0]->getOutput());
    $this->assertEquals("bar\n", $processes[1]->getOutput());
  }

  public function testFixMaxParallel()
  {
     $arr = array_fill(0, 5, 'test');
     $max_parallel = $this->process_manager->fixMaxParallel($arr, 5);
     $this->assertEquals(5, $max_parallel);
     $max_parallel = $this->process_manager->fixMaxParallel($arr, 0);
     $this->assertEquals(5, $max_parallel);
     $max_parallel = $this->process_manager->fixMaxParallel($arr, 6);
     $this->assertEquals(5, $max_parallel);
  }

  public function testStartChildren()
  {
    $processes =  array(
       new Process('ls -l'),
       new Process('ls -l')
     );
    $started_process = $this->process_manager->startChildren($processes, 0);
    $this->assertEquals(0, $started_process);
    $started_process = $this->process_manager->startChildren($processes, 2);
    $this->assertEquals(2, $started_process);
  }

  public function testWaitFor()
  {
    $processes = array(
        new Process('php -r "sleep(4);"'),
        new Process('php -r "sleep(4);"')
    );
    foreach ($processes as $process) {
      $process->start();
    }
    $num_running_task = $this->process_manager->waitFor($processes);
    $this->assertEquals(2, $num_running_task);
    foreach ($processes as $process) {
      $process->stop(1);
    }
  }
}
/* End of file ProcessManagerTest.php */
