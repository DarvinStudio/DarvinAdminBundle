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
 * Add menu item factories compiler pass
 */
class AddMenuItemFactoriesPass implements CompilerPassInterface
{
    const MENU_ID = 'darvin_admin.menu';

    const TAG_MENU_ITEM_FACTORY = 'darvin_admin.menu_item_factory';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MENU_ID)) {
            return;
        }

        $menuDefinition = $container->getDefinition(self::MENU_ID);

        foreach ($container->findTaggedServiceIds(self::TAG_MENU_ITEM_FACTORY) as $id => $attr) {
            $menuDefinition->addMethodCall('addItemFactory', [
                new Reference($id),
            ]);
        }
    }
}
