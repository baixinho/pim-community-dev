<?php

namespace Pim\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterUpdateGuessersPass implements CompilerPassInterface
{
    const VERSION_FILTER_REGISTRY = 'pim_versioning.doctrine.query.registry';

    const VERSION_FILTER_TAG = 'pim_versioning.doctrine.query.filter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::VERSION_FILTER_REGISTRY)) {
            return;
        }

        $service = $container->getDefinition(self::VERSION_FILTER_REGISTRY);

        $taggedServices = $container->findTaggedServiceIds(self::VERSION_FILTER_TAG);

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('register', [new Reference($id)]);
        }
    }
}
