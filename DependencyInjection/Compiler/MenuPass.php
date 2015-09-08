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
        $menu = $container->getDefinition('darvin_admin.menu');

        $menuItems = $container->findTaggedServiceIds(self::TAG_MENU_ITEM);
        uasort($menuItems, function (array $a, array $b) {
            if ($a[0]['position'] === $b[0]['position']) {
                return 0;
            }

            return $a[0]['position'] > $b[0]['position'] ? 1 : -1;
        });

        foreach ($menuItems as $id => $attr) {
            $menu->addMethodCall('addItem', array(
                $attr[0]['group'],
                new Reference($id),
            ));
        }
    }
}
