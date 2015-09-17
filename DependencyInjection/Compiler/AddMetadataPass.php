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
 * Add metadata compiler pass
 */
class AddMetadataPass implements CompilerPassInterface
{
    const TAG_METADATA = 'darvin_admin.metadata';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $metadataIds = $container->findTaggedServiceIds(self::TAG_METADATA);

        if (empty($metadataIds)) {
            return;
        }

        $poolDefinition = $container->getDefinition('darvin_admin.metadata.pool');

        foreach ($metadataIds as $id => $attr) {
            $poolDefinition->addMethodCall('add', array(
                new Reference($id),
            ));
        }
    }
}
