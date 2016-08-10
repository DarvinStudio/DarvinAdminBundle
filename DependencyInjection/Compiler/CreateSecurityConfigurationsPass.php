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

use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool;
use Darvin\ConfigBundle\DependencyInjection\Compiler\AddConfigurationsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create security configurations compiler pass
 */
class CreateSecurityConfigurationsPass implements CompilerPassInterface
{
    const PARENT_ID = 'darvin_admin.security.configuration.abstract';

    const POOL_ID = 'darvin_admin.security.configuration.pool';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $definitions = [];

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $definitions[$section->getSecurityConfigId()] = (new DefinitionDecorator(self::PARENT_ID))
                ->setArguments([
                    $section->getSecurityConfigName(),
                    $section->getAlias(),
                    $section->getEntity(),
                ]);
        }

        $container->addDefinitions($definitions);

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($definitions as $id => $definition) {
            $poolDefinition->addMethodCall(SecurityConfigurationPool::ADD_METHOD, [
                new Reference($id),
            ]);
        }

        (new AddConfigurationsPass())->addConfigurations($container, array_keys($definitions));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private function getSectionConfiguration(ContainerInterface $container)
    {
        return $container->get('darvin_admin.configuration.section');
    }
}
