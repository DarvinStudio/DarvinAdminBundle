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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Create controllers compiler pass
 */
class CreateControllersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $allMeta = $this->getMetadataManager($container)->getAll();

        if (empty($allMeta)) {
            return;
        }

        $definitions = array();

        foreach ($allMeta as $entityClass => $meta) {
            $definition = new DefinitionDecorator('darvin_admin.crud.controller');
            $definition->setArguments(array(
                $entityClass,
            ));

            $configuration = $meta->getConfiguration();

            if (!$meta->hasParent() && !$configuration['menu']['skip']) {
                $definition->addTag(AddMenuItemsPass::TAG_MENU_ITEM, array(
                    'group'    => $configuration['menu']['group'],
                    'position' => $configuration['menu']['position'],
                ));
            }

            $definitions[$meta->getControllerId()] = $definition;
        }

        $container->addDefinitions($definitions);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     *
     * @return \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private function getMetadataManager(ContainerInterface $container)
    {
        return $container->get('darvin_admin.metadata.manager');
    }
}
