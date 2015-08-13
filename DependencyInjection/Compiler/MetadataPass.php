<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 06.08.15
 * Time: 16:40
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Metadata compiler pass
 */
class MetadataPass implements CompilerPassInterface
{
    const TAG_METADATA = 'darvin_admin.metadata';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('darvin_admin.metadata.pool');

        foreach ($container->findTaggedServiceIds(self::TAG_METADATA) as $id => $attr) {
            $pool->addMethodCall('add', array(
                new Reference($id),
            ));
        }
    }
}
