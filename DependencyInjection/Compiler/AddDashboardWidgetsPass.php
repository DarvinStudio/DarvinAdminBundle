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
use Darvin\Utils\DependencyInjection\ServiceSorter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add dashboard widgets compiler pass
 */
class AddDashboardWidgetsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $blacklist = $container->getParameter('darvin_admin.dashboard.blacklist');
        $dashboard = $container->getDefinition('darvin_admin.dashboard');

        foreach (array_keys((new ServiceSorter())->sort($container->findTaggedServiceIds(DarvinAdminExtension::TAG_DASHBOARD_WIDGET))) as $id) {
            if (!in_array($id, $blacklist)) {
                $dashboard->addMethodCall('addWidget', [new Reference($id)]);
            }
        }
    }
}
