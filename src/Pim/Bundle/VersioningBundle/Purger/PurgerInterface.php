<?php

namespace Pim\Bundle\VersioningBundle\Purger;

/**
 * Version repository interface
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersioningPurgerInterface
{
    /**
     * Purge resources depending on a context
     *
     * @param array $context
     */
    public function purgeResources(array $context);
}
