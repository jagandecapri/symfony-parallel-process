<?php
namespace Jack\Symfony;

use Symfony\Component\Process\Process;

class ProcessManager
{
    /**
     * @var Process[]
     */
    protected $processes;

    public function runParallel(array $processes, $maxParallel, $poll = 1000)
    {
        $this->validateProcesses($processes);

        $this->processes = $processes;

        if (empty($processes)) {
            throw new \Exception('Can not run in parallel 0 commands');
        }
        $maxParallel = $this->fixMaxParallel($processes, $maxParallel);

        $queue = array_chunk($processes, $maxParallel);

        foreach ($queue as $processBatch) {
            $this->startChildren($processBatch, $maxParallel);
            do {
                usleep($poll);
            } while ($this->waitFor($processBatch));
        }
    }

    public function validateProcesses($processes)
    {
        foreach ($processes as $process) {
            if (!($process instanceof Process)) {
                throw new \InvalidArgumentException('Process in array need to be instance of Symfony Process');
            }
        }
    }

    public function fixMaxParallel($processes, $maxParallel)
    {
        $numProcesses = count($processes);
        if ($maxParallel <= 0 || $maxParallel > $numProcesses) {
            $maxParallel = $numProcesses;
        }
        return $maxParallel;
    }

    public function startChildren(array $processes, $maxParallel)
    {
        $started = 0;
        for ($i = 0; $i < $maxParallel; $i++) {
            $processes[$i]->start();
            $started++;
        }
        return $started;
    }

    public function waitFor(array $processes)
    {
        $numRunningTask = 0;
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $numRunningTask++;
            }
        }
        return $numRunningTask;
    }

    public function isProcessesRunning()
    {
        $running = false;
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $running = true;
                break;
            }
        }
        return $running;
    }
}
