<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Darvin\AdminBundle\Configuration\SectionConfiguration;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create security configurations compiler pass
 */
class CreateSecurityConfigurationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = [];
        $pool        = $container->getDefinition('darvin_admin.security.configuration.pool');

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $definition = new ChildDefinition('darvin_admin.security.configuration.abstract');
            $definition->setArguments([
                $section->getSecurityConfigName(),
                $section->getAlias(),
                $section->getEntity(),
            ]);
            $definition->addTag('darvin_config.configuration');

            $definitions[$section->getSecurityConfigId()] = $definition;

            $pool->addMethodCall('addConfiguration', [new Reference($section->getSecurityConfigId())]);
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private function getSectionConfiguration(ContainerInterface $container): SectionConfiguration
    {
        return $container->get('darvin_admin.configuration.section');
    }
}
