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

use Darvin\AdminBundle\Metadata\MetadataPool;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Create metadata compiler pass
 */
class CreateMetadataPass implements CompilerPassInterface
{
    const PARENT_ID  = 'darvin_admin.metadata.abstract';

    const POOL_ID = 'darvin_admin.metadata.pool';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = [];

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $definitions[$section->getMetadataId()] = (new DefinitionDecorator(self::PARENT_ID))
                ->setArguments([
                    $section->getEntity(),
                    $section->getConfig(),
                    $section->getAlias(),
                    $section->getControllerId(),
                ]);
        }

        $container->addDefinitions($definitions);

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($definitions as $id => $definition) {
            $poolDefinition->addMethodCall(MetadataPool::ADD_METHOD, [
                new Reference($id),
            ]);
        }
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
