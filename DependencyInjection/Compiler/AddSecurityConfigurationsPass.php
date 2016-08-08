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

use Darvin\ConfigBundle\DependencyInjection\Compiler\AddConfigurationsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add security configurations compiler pass
 */
class AddSecurityConfigurationsPass implements CompilerPassInterface
{
    const POOL_ID = 'darvin_admin.security.configuration.pool';

    const TAG_SECURITY_CONFIGURATION = 'darvin_admin.security_configuration';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::POOL_ID)) {
            return;
        }

        $configurationIds = $container->findTaggedServiceIds(self::TAG_SECURITY_CONFIGURATION);

        if (empty($configurationIds)) {
            return;
        }

        $poolDefinition = $container->getDefinition(self::POOL_ID);

        foreach ($configurationIds as $id => $attr) {
            $poolDefinition->addMethodCall('addConfiguration', [
                new Reference($id),
            ]);
        }

        (new AddConfigurationsPass())->addConfigurations($container, $configurationIds);
    }
}
