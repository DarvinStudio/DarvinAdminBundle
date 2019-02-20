<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Darvin\AdminBundle\Configuration\SectionConfiguration;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create metadata compiler pass
 */
class CreateMetadataPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];
        $pool        = $container->getDefinition('darvin_admin.metadata.pool');

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            if (null === $section->getConfig()) {
                continue;
            }

            $definition = new ChildDefinition('darvin_admin.metadata.abstract');
            $definition->setArguments([
                $section->getAlias(),
                $section->getEntity(),
                $section->getConfig(),
                $section->getControllerId(),
            ]);

            $definitions[$section->getMetadataId()] = $definition;

            $pool->addMethodCall('addMetadata', [new Reference($section->getMetadataId())]);
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Psr\Container\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private function getSectionConfiguration(ContainerInterface $container): SectionConfiguration
    {
        return $container->get('darvin_admin.configuration.section');
    }
}
