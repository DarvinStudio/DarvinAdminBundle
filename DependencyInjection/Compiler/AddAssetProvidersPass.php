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
 * Add asset providers to pool compiler pass
 */
class AddAssetProvidersPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.asset.provider.pool';

    const TAG_ASSETS_PROVIDER = 'darvin_admin.assets_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($container->findTaggedServiceIds(self::TAG_ASSETS_PROVIDER) as $id => $tags) {
            $reference = new Reference($id);

            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    $message = sprintf(
                        'Tag "%s" of service "%s" misconfigured: attribute "alias" must be provided.',
                        self::TAG_ASSETS_PROVIDER,
                        $id
                    );

                    throw new \InvalidArgumentException($message);
                }

                $poolDefinition->addMethodCall('addProvider', array(
                    $tag['alias'],
                    $reference,
                ));
            }
        }
    }
}
