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
 * Add security configurations compiler pass
 */
class AddSecurityConfigurationsPass implements CompilerPassInterface
{
    const TAG_SECURITY_CONFIGURATION = 'darvin_admin.security_configuration';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configurationIds = $container->findTaggedServiceIds(self::TAG_SECURITY_CONFIGURATION);

        if (empty($configurationIds)) {
            return;
        }

        $taggedServiceIdsSorter = new TaggedServiceIdsSorter();
        $taggedServiceIdsSorter->sort($configurationIds);

        $poolDefinition = $container->getDefinition('darvin_admin.security.configuration.pool');

        foreach ($configurationIds as $id => $attr) {
            $poolDefinition->addMethodCall('add', array(
                new Reference($id),
            ));
        }
    }
}
