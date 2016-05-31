<?php

namespace Pim\Bundle\VersioningBundle\Purger\ORM;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\VersioningBundle\Purger\VersioningPurgerInterface;

/**
 * 
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersioningPurger extends EntityRepository implements VersioningPurgerInterface
{

    private $extraConditions;


    public function __construct(array $extraJoins, array $extraCondition)
    {

    }

    public function getQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        foreach ($this->extraConditions as $extraCondition) {
            $
        }
    }

    /**
     * {@inheritdoc}
     */
    public function purgeResources(array $context)
    {
        /**
         *
        SELECT *
        FROM pim_versioning_version v1
        WHERE
        v1.resource_name LIKE '%Product'
        AND v1.version > 1
        AND v1.id NOT IN (
            SELECT v2.id
            FROM pim_versioning_version v2
            INNER JOIN
            (
                SELECT resource_id, MAX(version) AS MaxVersion
                FROM pim_versioning_version
                GROUP BY resource_id
            ) grouped_pim_versioning_version
        ON v2.resource_id = grouped_pim_versioning_version.resource_id
        AND v2.version = grouped_pim_versioning_version.MaxVersion
        )
        ;

         */
        $qb = $this->createQueryBuilder()
            ->select()
            ->from('pim_versioning_version', 'v1')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('v1.resource_name = :resourceName'),
                    $qb->expr()->gt('v1.resource_name = 1'),
                    $qb->expr()->nin(
                        'v1.id',
                        $qb2->createQueryBuilder()
                            ->select('v2.id')
                            ->from('pim_versioning_version', 'v2')
                            ->join(
                                $qb3->createQueryBuilder()
                                    ->select('resourceId, MAX(version) as MAX_VERSION')
                                    ->groupBy('resourceId')
                                ,
                                'grouped_pim_versioning_version'
                                $qb2->expr()->andX(
                                    $qb2->expr()->eq('v2.resource_id', 'grouped_pim_versioning_version.resource_id'),
                                    $qb2->expr()->eq('v2.version', 'grouped_pim_versioning_version.MaxVersion')
                                )
                            )
                        )
                );


        $qb->getQuery()->execute();
    }
}
