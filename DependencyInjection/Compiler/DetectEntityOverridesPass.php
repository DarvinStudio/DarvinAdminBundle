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

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $meta */
        foreach ($this->getEntityManager($container)->getMetadataFactory()->getAllMetadata() as $meta) {
            if (0 !== strpos($meta->getName(), 'Darvin\\')) {
                continue;
            }

            preg_match('/^Darvin\\\(.*)Bundle\\\Entity\\\(.*)$/', $meta->getName(), $matches);

            if (3 !== count($matches)) {
                continue;
            }

            $parts = array_merge(['AppBundle', 'Entity', $matches[1]], explode('\\', $matches[2]));
            $tail = array_pop($parts);
            $parts[] = 'App'.$tail;
            $replacement = implode('\\', $parts);

            if (!class_exists($replacement) || !in_array($meta->getName(), class_parents($replacement))) {
                continue;
            }

            $overrides[$meta->getName()] = $replacement;
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
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager(ContainerInterface $container)
    {
        return $container->get('doctrine.orm.entity_manager');
    }
}
