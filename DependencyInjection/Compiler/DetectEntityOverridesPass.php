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
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $overrides = [];

        foreach ($this->getSectionConfig($container)->getSections() as $section) {
            if (0 !== strpos($section->getEntity(), 'Darvin\\')) {
                continue;
            }

            preg_match('/^Darvin\\\(.*)Bundle\\\Entity\\\(.*)$/', $section->getEntity(), $matches);

            if (3 !== count($matches)) {
                continue;
            }

            $parts = array_merge(['AppBundle', 'Entity', $matches[1]], explode('\\', $matches[2]));
            $tail = array_pop($parts);
            $parts[] = 'App'.$tail;
            $replacement = implode('\\', $parts);

            if (!class_exists($replacement) || !in_array($section->getEntity(), class_parents($replacement))) {
                continue;
            }

            $overrides[$section->getEntity()] = $replacement;
        }
        if (!empty($overrides)) {
            $container->prependExtensionConfig('darvin_admin', [
                'entity_override' => $overrides,
            ]);
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private function getSectionConfig(ContainerInterface $container)
    {
        return $container->get('darvin_admin.configuration.section');
    }
}
