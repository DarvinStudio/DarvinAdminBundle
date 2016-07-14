<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
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
 * Add asset compilers to pool compiler pass
 */
class AddAssetCompilersPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.asset.compiler.pool';

    const TAG_ASSET_COMPILER = 'darvin_admin.asset_compiler';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($container->findTaggedServiceIds(self::TAG_ASSET_COMPILER) as $id => $tags) {
            $reference = new Reference($id);

            foreach ($tags as $tag) {
                $poolDefinition->addMethodCall('addCompiler', [
                    $reference,
                ]);
            }
        }
    }
}
