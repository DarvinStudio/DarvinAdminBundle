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
 * Add view widgets to pool compiler pass
 */
class AddViewWidgetsPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.view.widget.pool';

    const TAG_VIEW_WIDGET = 'darvin_admin.view_widget';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $widgetIds = $container->findTaggedServiceIds(self::TAG_VIEW_WIDGET);

        if (empty($widgetIds)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($widgetIds as $id => $attr) {
            $poolDefinition->addMethodCall('addWidget', [
                new Reference($id),
            ]);
        }
    }
}
