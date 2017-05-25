<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add resolve target entities compiler pass
 */
class AddResolveTargetEntitiesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('doctrine')) {
            return;
        }

        $resolveTargetEntities = [];

        foreach ($container->getExtensionConfig('darvin_admin') as $config) {
            if (!isset($config['entity_override'])) {
                continue;
            }
            foreach ($config['entity_override'] as $target => $replacement) {
                foreach (class_implements($target) as $interface) {
                    if ($interface === $target.'Interface') {
                        $resolveTargetEntities[$interface] = $replacement;
                    }
                }
            }
        }
        if (!empty($resolveTargetEntities)) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'resolve_target_entities' => $resolveTargetEntities,
                ],
            ]);
        }
    }
}
