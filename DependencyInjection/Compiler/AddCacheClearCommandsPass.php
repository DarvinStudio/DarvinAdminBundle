<?php declare(strict_types=1);
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
 * Add commands to cache clearer compiler pass
 */
class AddCacheClearCommandsPass implements CompilerPassInterface
{
    private const CLEARER = 'darvin_admin.cache.clear.clearer';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::CLEARER)) {
            return;
        }

        $clearer = $container->getDefinition(self::CLEARER);

        foreach ($container->getParameter('darvin_admin.cache.clear.sets') as $setName => $setAttr) {
            if (!$setAttr['enabled']) {
                continue;
            }
            foreach ($setAttr['commands'] as $commandAlias => $commandAttr) {
                if (!$commandAttr['enabled']) {
                    continue;
                }

                $clearer->addMethodCall('addCommand', [
                    $setName,
                    $commandAlias,
                    new Reference($commandAttr['id']),
                    $commandAttr['input'],
                ]);
            }
        }
    }
}
