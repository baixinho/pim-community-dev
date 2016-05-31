<?php

namespace Pim\Component\Versioning\Query\Filters;

/**
 * Aims to register and retrieve filters useable on the VersionPurger
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionFilterRegistryInterface
{
    public function register(VersionFilterInterface $versionFilter);
}
