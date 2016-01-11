<?php
namespace Jack\Symfony;

use Symfony\Component\Process\Process;

/**
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component.
 */
class ProcessManager
{
    /**
     * @var Process[]
     */
    protected $processes;

    /**
     * @param Process[] $processes
     * @param int $maxParallel
     * @param int $poll
     */
    public function runParallel(array $processes, $maxParallel, $poll = 1000)
    {
        $this->validateProcesses($processes);

        $this->processes = $processes;

        $queue = array_chunk($processes, $maxParallel);

        foreach ($queue as $processBatch) {
            $batchLimit = $this->fixMaxParallel($processBatch, $maxParallel);

            $this->startChildren($processBatch, $batchLimit);
            do {
                usleep($poll);
            } while ($this->getNumberOfRunningTasks($processBatch));
        }
    }

    /**
     * @param Process[] $processes
     */
    protected function validateProcesses($processes)
    {
        if (empty($processes)) {
            throw new \InvalidArgumentException('Can not run in parallel 0 commands');
        }

        foreach ($processes as $process) {
            if (!($process instanceof Process)) {
                throw new \InvalidArgumentException('Process in array need to be instance of Symfony Process');
            }
        }
    }

    /**
     * @param Process[] $processes
     * @param int $maxParallel
     * @return int
     */
    protected function fixMaxParallel($processes, $maxParallel)
    {
        $processesCount = count($processes);
        if ($maxParallel <= 0 || $maxParallel > $processesCount) {
            $maxParallel = $processesCount;
        }
        return $maxParallel;
    }

    /**
     * @param Process[] $processes
     * @param int $maxParallel
     * @return int
     */
    protected function startChildren(array $processes, $maxParallel)
    {
        $started = 0;
        for ($i = 0; $i < $maxParallel; $i++) {
            $processes[$i]->start();
            $started++;
        }
        return $started;
    }

    /**
     * @param Process[] $processBatch
     * @return int
     */
    protected function getNumberOfRunningTasks(array $processBatch)
    {
        $numRunningTask = 0;
        foreach ($processBatch as $process) {
            if ($process->isRunning()) {
                $numRunningTask++;
            }
        }
        return $numRunningTask;
    }
}
