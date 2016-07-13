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

use Darvin\Utils\DependencyInjection\TaggedServiceIdsSorter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add menu items compiler pass
 */
class AddMenuItemsPass implements CompilerPassInterface
{
    const MENU_ID = 'darvin_admin.menu';

    const TAG_MENU_ITEM = 'darvin_admin.menu_item';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MENU_ID)) {
            return;
        }

        $items = $container->findTaggedServiceIds(self::TAG_MENU_ITEM);

        if (empty($items)) {
            return;
        }

        $sorter = new TaggedServiceIdsSorter();
        $sorter->sort($items);

        $menuDefinition = $container->getDefinition(self::MENU_ID);

        foreach ($items as $id => $attr) {
            $menuDefinition->addMethodCall('addItem', [
                new Reference($id),
                isset($attr[0]['group']) ? $attr[0]['group'] : null,
            ]
            );
        }
    }
}
