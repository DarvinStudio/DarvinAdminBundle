<?php
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
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
 * Add cache clear command compiler pass
 */
class AddCacheClearCommandsPass implements CompilerPassInterface
{
    private const CACHE_CLEANER_ID = 'darvin_admin.cache.clearer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CACHE_CLEANER_ID)) {
            return;
        }

        $sets = $container->getParameter('darvin_admin.cache.clear.sets');

        if (empty($sets)) {
            return;
        }

        $cacheClearerDefinition = $container->getDefinition(self::CACHE_CLEANER_ID);

        $definitions = [];

        foreach ($sets as $set => $commands) {
            foreach ($commands as $alias => $command) {
                $id = strpos($command['id'], '@') === 0 ? substr($command['id'], 1) : $command['id'];
                $cacheClearerDefinition->addMethodCall('addCommand', [
                    $set,
                    $alias,
                    new Reference($id),
                    $command['input'],
                ]);
            }
        }

        $container->addDefinitions($definitions);
    }
}
