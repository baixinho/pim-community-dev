<?php

namespace spec\Pim\Bundle\VersioningBundle\Manager;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Component\Versioning\Model\Version;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VersionManagerSpec extends ObjectBehavior
{
    function let(
        SmartManagerRegistry $registry,
        VersionBuilder $builder,
        ObjectManager $om,
        VersionRepositoryInterface $versionRepository,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($registry, $builder, $versionContext, $eventDispatcher);

        $registry->getManagerForClass(Argument::any())->willReturn($om);
        $registry->getRepository(Argument::any())->willReturn($versionRepository);
        $versionRepository->findBy(Argument::cetera())->willReturn([]);
        $versionRepository->getNewestLogEntry(Argument::cetera())->willReturn(null);
    }

    function it_is_aware_of_the_versioning_mode()
    {
        $this->isRealTimeVersioning()->shouldReturn(true);
        $this->setRealTimeVersioning(false);
        $this->isRealTimeVersioning()->shouldReturn(false);
    }

    function it_uses_version_builder_to_build_versions($builder, ProductInterface $product)
    {
        $this->setUsername('julia');
        $this->buildVersion($product);

        $builder->buildVersion($product, 'julia', null, null)->shouldHaveBeenCalled();
    }

    function it_builds_versions_for_versionable_entities(ProductInterface $product, $builder)
    {
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $versions[0]->shouldBeAnInstanceOf('Akeneo\Component\Versioning\Model\Version');
    }

    function it_creates_pending_versions_when_real_time_versioning_is_disabled(ProductInterface $product, $builder)
    {
        $this->setRealTimeVersioning(false);
        $builder->createPendingVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $version = $versions[0];
        $version->shouldBeAnInstanceOf('Akeneo\Component\Versioning\Model\Version');
        $version->isPending()->shouldReturn(true);
    }

    function it_builds_pending_versions_and_last_version_when_versioning_an_entity(ProductInterface $product, $builder, $versionRepository)
    {
        $product->getId()->willReturn(1);

        $pending1 = new Version('Product', 1, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $versionRepository->findBy(Argument::cetera())->willReturn([$pending1, $pending2]);

        $builder->buildPendingVersion($pending1, null)->willReturn($pending1)->shouldBeCalled();
        $builder->buildPendingVersion($pending2, $pending1)->willReturn($pending2)->shouldBeCalled();
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('Product', 1, 'julia'))->shouldBeCalled();

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(3);
    }

    function it_builds_pending_versions_for_a_given_entity(ProductInterface $product, $builder, $versionRepository)
    {
        $product->getId()->willReturn(1);

        $pending1 = new Version('Product', 1, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $versionRepository->findBy(Argument::cetera())->willReturn([$pending1, $pending2]);

        $builder->buildPendingVersion($pending1, null)->willReturn($pending1)->shouldBeCalled();
        $builder->buildPendingVersion($pending2, $pending1)->willReturn($pending2)->shouldBeCalled();

        $versions = $this->buildPendingVersions($product);
        $versions->shouldHaveCount(2);
    }

    function it_lists_the_versions_by_date_and_resource_name($versionRepository, Version $version1)
    {
        $resourceName = 'product';
        $operator = VersionManager::OPERATOR_DATE_OLDER;

        $versionRepository->getResourcesByDate($resourceName, $operator, Argument::type('\Datetime'))
            ->shouldBecalled()
            ->willReturn([$version1]);

        $this->getVersionsByDate($resourceName, $operator, 31)->shouldReturn([$version1]);
    }

    function it_purges_the_versions_by_date_and_resource_name($versionRepository, Version $version1, Version $version2)
    {
        $resourceName = 'product';
        $resourceId = 12;

        $version1->getId()->willReturn(1);
        $version1->getResourceName()->willReturn($resourceName);
        $version1->getResourceId()->willReturn($resourceId);

        $version2->getId()->willReturn(2);
        $version2->getResourceName()->willReturn($resourceName);
        $version2->getResourceId()->willReturn(12);

        $versionRepository
            ->getNewestLogEntry($resourceName, $resourceId)
            ->shouldBeCalledTimes(2)
            ->willReturn($version2);

        $versionRepository->deleteResources([$version1])->shouldBecalled();

        $this->purgeVersions([$version1, $version2]);
    }
}
