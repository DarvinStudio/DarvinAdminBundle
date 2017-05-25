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
 * Detect entity overrides compiler pass
 */
class DetectEntityOverridesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $entityOverride = [];

        foreach ($container->getParameter('darvin_admin.sections') as $section) {
            $target = $section['entity'];

            if (0 !== strpos($target, 'Darvin\\')) {
                continue;
            }

            preg_match('/^Darvin\\\(.*)Bundle\\\Entity\\\(.*)$/', $target, $matches);

            if (3 !== count($matches)) {
                continue;
            }

            $parts = array_merge(['AppBundle', 'Entity', $matches[1]], explode('\\', $matches[2]));
            $tail = array_pop($parts);
            $parts[] = 'App'.$tail;
            $replacement = implode('\\', $parts);

            if (!class_exists($replacement) || !in_array($target, class_parents($replacement))) {
                continue;
            }

            $entityOverride[$target] = $replacement;
        }
        if (!empty($entityOverride)) {
            $container->setParameter(
                'darvin_admin.entity_override',
                array_merge($container->getParameter('darvin_admin.entity_override'), $entityOverride)
            );
        }
    }
}
