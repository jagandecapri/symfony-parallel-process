<?php
namespace Jack\Symfony;

use Symfony\Component\Process\Process;

class ProcessManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * 
     */
    public function setUp()
    {
        $this->processManager = new ProcessManager();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRunParallelWithZeroProcesses()
    {
        $this->processManager->runParallel(array(), 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRunParallelWithNonSymfonyProcess()
    {
        $this->processManager->runParallel(array('ls -la'), 0);
    }

    /**
     *
     */
    public function testRunParallel()
    {
        $processes = array(
            new Process('echo foo'),
            new Process('echo bar'),
        );
        $this->processManager->runParallel($processes, 2, 1000);

        $this->assertEquals('foo' . PHP_EOL, $processes[0]->getOutput());
        $this->assertEquals('bar' . PHP_EOL, $processes[1]->getOutput());
    }
}
