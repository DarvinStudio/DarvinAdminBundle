<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 04.08.15
 * Time: 16:50
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Menu compiler pass
 */
class MenuPass implements CompilerPassInterface
{
    const TAG_MENU_ITEM = 'darvin_admin.menu_item';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $menu = $container->getDefinition('darvin_admin.menu.menu');

        foreach ($container->findTaggedServiceIds(self::TAG_MENU_ITEM) as $id => $attr) {
            $menu->addMethodCall('addItem', array(
                new Reference($id),
            ));
        }
    }
}
