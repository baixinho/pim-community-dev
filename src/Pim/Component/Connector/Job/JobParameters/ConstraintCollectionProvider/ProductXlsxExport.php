<?php

namespace Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Constraints for product XLSX export
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductXlsxExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simpleXlsx
     * @param array                                 $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simpleXlsx, array $supportedJobNames)
    {
        $this->simpleProvider = $simpleXlsx;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();
        $constraintFields['channel'] = [
            new NotBlank(['groups' => 'Execution']),
            new Channel()
        ];

        $constraintFields['locales'] = new NotBlank([
            'groups'  => 'Execution',
            'message' => 'pim_connector.export.locales.validation.not_blank'
        ]);

        $constraintFields['enabled'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['updated'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['completeness'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['linesPerFile'] = [
            new NotBlank(),
            new GreaterThan(1)
        ];
        $constraintFields['families'] = [];

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
