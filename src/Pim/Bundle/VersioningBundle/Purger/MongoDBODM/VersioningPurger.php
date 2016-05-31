<?php

namespace Pim\Bundle\VersioningBundle\Purger\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\VersioningBundle\Purger\VersioningPurgerInterface;

/**
 * Purges the history of versions for entities in mongodb
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersioningPurger extends DocumentRepository implements VersioningPurgerInterface
{
    /** @staticvar string */
    const MAX_VERSION_FIELD = 'MAX_VERSION';
    /**
     * {@inheritdoc}
     */
    public function purgeResources(array $context)
    {
        $resourceName = $context['resourceName'];
        $maxVersions = $this->getMaxVersionForResources($resourceName);
        $this->purgeVersions($resourceName, $maxVersions);
    }

    /**
     * @return string
     */
    public function getDocumentName()
    {
        return 'Akeneo\Component\Versioning\Model\Version';
    }

    /**
     * Search the last version number for each resource
     *
     * @param string $resourceName
     *
     * @return array
     */
    protected function getMaxVersionForResources($resourceName)
    {
        $documentName = $this->getDocumentName();
        $ab = $this->dm->getDocumentCollection($documentName)->createAggregationBuilder();
        $ab->match()
            ->field('resourceName')
            ->equals($resourceName)
            ->field('version')
            ->gt(1)
            ->group()
            ->field('_id')
            ->expression('$resourceId')
            ->field(self::MAX_VERSION_FIELD)
            ->max('$version');

        return $ab->execute();
    }

    /**
     * Removes the
     *
     * @param $resourceName
     * @param $maxVersions
     */
    protected function purgeVersions($resourceName, $maxVersions)
    {
        $qb = $this->createQueryBuilder()
            ->remove()
            ->field('resourceName')->equals($resourceName)
            ->field('version')->gt(1);

        foreach ($maxVersions as $maxVersion) {
            $qb->addOr(
                $qb->expr()
                    ->field('resourceId')->equals($maxVersion['_id'])
                    ->field('version')->lt($maxVersion[self::MAX_VERSION_FIELD])
            );
        }

        $qb->getQuery()->execute();
    }
}
