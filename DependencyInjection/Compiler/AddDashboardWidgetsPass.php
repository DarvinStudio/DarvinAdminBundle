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
 * Add dashboard widgets compiler pass
 */
class AddDashboardWidgetsPass implements CompilerPassInterface
{
    const TAG_DASHBOARD_WIDGET = 'darvin_admin.dashboard_widget';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $widgetIds = $container->findTaggedServiceIds(self::TAG_DASHBOARD_WIDGET);

        if (empty($widgetIds)) {
            return;
        }

        $taggedServiceIdsSorter = new TaggedServiceIdsSorter();
        $taggedServiceIdsSorter->sort($widgetIds);

        $dashboardDefinition = $container->getDefinition('darvin_admin.dashboard.dashboard');

        foreach ($widgetIds as $id => $attr) {
            $dashboardDefinition->addMethodCall('addWidget', array(
                new Reference($id),
            ));
        }
    }
}
