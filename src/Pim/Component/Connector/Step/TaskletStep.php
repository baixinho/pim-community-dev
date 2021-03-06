<?php

namespace Pim\Component\Connector\Step;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskletStep extends AbstractStep
{
    /** @var TaskletInterface */
    protected $tasklet;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->tasklet->setStepExecution($stepExecution);
        $this->tasklet->execute();
    }

    /**
     * @return TaskletInterface
     */
    public function getTasklet()
    {
        return $this->tasklet;
    }

    /**
     * @param TaskletInterface $tasklet
     */
    public function setTasklet($tasklet)
    {
        $this->tasklet = $tasklet;
    }
}
