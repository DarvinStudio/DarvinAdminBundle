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
class AddCacheClearCommandPass implements CompilerPassInterface
{
    const CACHE_CLEANER_ID = 'darvin_admin.cache.cleaner';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CACHE_CLEANER_ID)) {
            return;
        }

        $clearCommandTypes = $container->getParameter('darvin_admin.cache.clear.commands');

        if (empty($clearCommandTypes)) {
            return;
        }

        $cacheCleanerDefinition = $container->getDefinition(self::CACHE_CLEANER_ID);

        $definitions = [];

        foreach ($clearCommandTypes as $type => $clearCommands) {
            foreach ($clearCommands as $name => $clearCommand) {
                $cacheCleanerDefinition->addMethodCall('addCacheClearCommand', [
                    $type,
                    $name,
                    new Reference(str_replace('@', '', $clearCommand['alias'])),
                    $clearCommand['input'],
                ]);
            }
        }

        $container->addDefinitions($definitions);
    }
}
