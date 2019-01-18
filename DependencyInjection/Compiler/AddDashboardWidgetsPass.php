<?php declare(strict_types=1);
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
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $blacklist = $container->getParameter('darvin_admin.dashboard.blacklist');
        $dashboard = $container->getDefinition('darvin_admin.dashboard');
        $ids       = $container->findTaggedServiceIds('darvin_admin.dashboard_widget');

        (new TaggedServiceIdsSorter())->sort($ids);

        foreach (array_keys($ids) as $id) {
            if (!in_array($id, $blacklist)) {
                $dashboard->addMethodCall('addWidget', [new Reference($id)]);
            }
        }
    }
}
