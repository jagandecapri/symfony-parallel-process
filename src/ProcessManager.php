<?php
namespace Jack\Symfony;

use Symfony\Component\Process\Process;

/**
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component.
 */
class ProcessManager
{
    /**
     * @param Process[] $processes
     * @param int $maxParallel
     * @param int $poll
     */
    public function runParallel(array $processes, $maxParallel, $poll = 1000)
    {
        $this->validateProcesses($processes);

        /** @var Process[][] $queue */
        $queue = array_chunk($processes, $maxParallel);

        foreach ($queue as $processBatch) {
            foreach ($processBatch as $process) {
                $process->start();
            }
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
