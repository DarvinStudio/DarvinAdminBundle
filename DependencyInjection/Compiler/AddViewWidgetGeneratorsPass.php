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
 * Add view widget generators compiler pass
 */
class AddViewWidgetGeneratorsPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.view.widget_generator.pool';

    const TAG_VIEW_WIDGET_GENERATOR = 'darvin_admin.view.widget_generator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $generatorIds = $container->findTaggedServiceIds(self::TAG_VIEW_WIDGET_GENERATOR);

        if (empty($generatorIds)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($generatorIds as $id => $attr) {
            $poolDefinition->addMethodCall('addWidgetGenerator', [
                new Reference($id),
            ]);
        }
    }
}
