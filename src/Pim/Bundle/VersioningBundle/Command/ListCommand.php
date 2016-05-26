<?php

namespace Pim\Bundle\VersioningBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\BaseConnectorBundle\Cache\CacheClearer;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * List version of data
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:versioning:list')
            ->setDescription('List versions of any updated entities')
            ->addArgument(
                'entity',
                null,
                InputArgument::OPTIONAL,
                'Show the versions of entity'
            )
            ->addOption(
                'more-than-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Versions older than the number of days',
                0
            )
            ->addOption(
                'less-than-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Versions younger than the number of days',
                0
            )
            ->addOption(
                'show-log',
                null,
                InputOption::VALUE_OPTIONAL,
                'display the log on the output'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');
        if (!$noDebug) {
            $logger = $this->getContainer()->get('logger');
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $entityType = $input->getArgument('entity');
        try {
            $resourceName = $this->getContainer()
                ->get('pim_catalog.resolver.fqcn')
                ->getFQCN($entityType);
        } catch (InvalidArgumentException $e) {
            $output->writeln(sprintf('<warning>"%s" is not a versionnable entity.</warning>', $entityType));

            return;
        }

        $numberOfDays = $input->getOption('more-than-days');
        $lessThanDays = $input->getOption('less-than-days');

        $versionManager = $this->getVersionManager();
        $dateOperator = $versionManager::OPERATOR_DATE_OLDER;

        if ($lessThanDays > $numberOfDays) {
            $dateOperator = $versionManager::OPERATOR_DATE_YOUNGER;
            $numberOfDays = $lessThanDays;
        } elseif (0 < $lessThanDays) {
            $output->writeln('<info>Warning. Both --more-than-days and --less-than-days options have been set. The option used will be --more-than-days.</info>');
        }

        $versions = $versionManager->getVersionsByDate($resourceName, $dateOperator, $numberOfDays);

        $table = new Table($output);
        $table->setHeaders(
            [
                sprintf('%s id', $entityType),
                'Author',
                'Date',
                'Version number',
                'Number of Changes',
                'is Pending',
            ]
        );

        $versionsInfo = [];
        foreach ($versions as $version) {
            $versionsInfo[] = [
                $version->getResourceId(),
                $version->getAuthor(),
                $version->getLoggedAt()->format('Y-m-d H:i:s'),
                $version->getVersion(),
                count($version->getChangeset()),
                $version->isPending() ? 'Yes' : 'No',
            ];
        }

        $table->setRows($versionsInfo);
        $table->render();

        $output->writeln(sprintf('<info>%s number of versions for entity %s.</info>', count($versions), $entityType));
    }

    /**
     * @return VersionManager
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this
            ->getContainer()
            ->get('pim_catalog.doctrine.smart_manager_registry')
            ->getManagerForClass($this->getVersionClass());
    }

    /**
     * @return CacheClearer
     */
    protected function getCacheClearer()
    {
        return $this->getContainer()->get('pim_transform.cache.product_cache_clearer');
    }

    /**
     * @return string
     */
    protected function getVersionClass()
    {
        return $this->getContainer()->getParameter('pim_versioning.entity.version.class');
    }
}
