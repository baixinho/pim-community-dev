<?php

namespace spec\Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($versionManager, $productSaver, $detacher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Writer\Doctrine\ProductWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_saves_items(
        $productSaver,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $this->write($items);
    }

    function it_increments_summary_info(
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $this->write([$product1, $product2]);
    }

    function it_clears_cache(
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write($items);
    }
}
