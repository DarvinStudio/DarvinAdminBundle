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
 * Controller compiler pass
 */
class ControllerPass implements CompilerPassInterface
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

        $controllers = array();

        foreach ($allMeta as $entityClass => $meta) {
            $controller = new DefinitionDecorator('darvin_admin.controller.crud');
            $controller->setArguments(array(
                $entityClass,
            ));

            if (!$meta->hasParent()) {
                $controller->addTag(MenuPass::TAG_MENU_ITEM);
            }

            $controllers[$meta->getControllerId()] = $controller;
        }

        $container->addDefinitions($controllers);
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
