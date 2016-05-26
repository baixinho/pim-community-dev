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
class PurgeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:versioning:purge')
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
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'display the log on the output'
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

        $isDryRun = $input->getOption('dry-run');

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
        $versions = $versionManager->purgeVersions($versions);
        if (0 === count($versions)) {
            $output->writeln(sprintf('<info>No versions found for entity %s. Nothing to do.</info>', count($versions)));

            return;
        }
        if ($isDryRun) {
            $output->writeln(sprintf('<info>Dry run mode activated. %s versions were found for deletion.</info>', count($versions)));

            return;
        }

        $output->writeln(sprintf('<info>Starting the deletion for entity %s.</info>', $entityType));
        $versionManager->purgeVersions($versions);
        $output->writeln(sprintf('<info>Successfully delete the versions of entity %s.</info>', $entityType));
    }

    /**
     * @return VersionManager
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }
}
