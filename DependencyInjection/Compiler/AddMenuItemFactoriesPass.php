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

use Darvin\AdminBundle\DependencyInjection\DarvinAdminExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add menu item factories compiler pass
 */
class AddMenuItemFactoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $menu = $container->getDefinition('darvin_admin.menu');

        foreach (array_keys($container->findTaggedServiceIds(DarvinAdminExtension::TAG_MENU_ITEM_FACTORY)) as $id) {
            $menu->addMethodCall('addItemFactory', [new Reference($id)]);
        }
    }
}
