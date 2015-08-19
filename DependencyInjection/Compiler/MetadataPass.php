<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
