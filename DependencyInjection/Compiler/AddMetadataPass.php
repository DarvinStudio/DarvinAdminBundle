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

use Darvin\AdminBundle\Metadata\MetadataFactory;
use Darvin\AdminBundle\Metadata\MetadataPool;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add metadata compiler pass
 */
class AddMetadataPass implements CompilerPassInterface
{
    const METADATA_FACTORY_ID = 'darvin_admin.metadata.factory';
    const METADATA_PARENT_ID  = 'darvin_admin.metadata.abstract';

    const POOL_ID = 'darvin_admin.metadata.pool';

    const TAG_METADATA = 'darvin_admin.metadata';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        $this->addMetadata($poolDefinition, array_keys($container->findTaggedServiceIds(self::TAG_METADATA)));

        $metaDefinitions = [];
        $metaFactoryReference = new Reference(self::METADATA_FACTORY_ID);

        foreach ($this->getSectionConfiguration($container)->getSections() as $section) {
            $metaDefinitions[$section->getMetadataId()] = (new DefinitionDecorator(self::METADATA_PARENT_ID))
                ->setFactory([$metaFactoryReference, MetadataFactory::CREATE_METHOD])
                ->setArguments([
                    $section->getEntity(),
                    $section->getConfig(),
                    $section->getAlias(),
                ]);
        }

        $container->addDefinitions($metaDefinitions);

        $this->addMetadata($poolDefinition, array_keys($metaDefinitions));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Definition $poolDefinition Metadata pool service definition
     * @param string[]                                          $ids            Metadata service IDs
     */
    private function addMetadata(Definition $poolDefinition, array $ids)
    {
        foreach ($ids as $id) {
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
