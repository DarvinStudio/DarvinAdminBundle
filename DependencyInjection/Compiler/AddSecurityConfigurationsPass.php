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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add security configurations compiler pass
 */
class AddSecurityConfigurationsPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.security.configuration.pool';

    const SECTION_CONFIGURATION_PARENT_ID = 'darvin_admin.security.configuration.entity.abstract';

    const TAG_SECURITY_CONFIGURATION = 'darvin_admin.security_configuration';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        $this->addConfigurations($poolDefinition, array_keys($container->findTaggedServiceIds(self::TAG_SECURITY_CONFIGURATION)), $container);

        $configurationDefinitions = [];

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $configurationDefinitions[$section->getSecurityConfigId()] = (new DefinitionDecorator(self::SECTION_CONFIGURATION_PARENT_ID))
                ->setArguments([
                    $section->getSecurityConfigName(),
                    $section->getAlias(),
                    $section->getEntity(),
                ]);
        }

        $container->addDefinitions($configurationDefinitions);

        $this->addConfigurations($poolDefinition, array_keys($configurationDefinitions), $container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Definition       $poolDefinition Security configuration pool service definition
     * @param string[]                                                $ids            Security configuration service IDs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container      DI container
     */
    private function addConfigurations(Definition $poolDefinition, array $ids, ContainerBuilder $container)
    {
        if (empty($ids)) {
            return;
        }
        foreach ($ids as $id) {
            $poolDefinition->addMethodCall(SecurityConfigurationPool::ADD_METHOD, [
                new Reference($id),
            ]);
        }

        (new AddConfigurationsPass())->addConfigurations($container, $ids);
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
