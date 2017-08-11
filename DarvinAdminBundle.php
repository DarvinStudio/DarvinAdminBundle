<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle;

use Darvin\AdminBundle\DependencyInjection\Compiler\AddCacheClearCommandsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddDashboardWidgetsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddMenuItemFactoriesPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddResolveTargetEntitiesPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddViewWidgetsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\CreateControllersPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\CreateMetadataPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\CreateSecurityConfigurationsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\DetectEntityOverridesPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\ReplaceTranslatableSubscriberPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Admin bundle
 */
class DarvinAdminBundle extends Bundle
{
    const VERSION = '5.12';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            // Order matters
            ->addCompilerPass(new DetectEntityOverridesPass())
            ->addCompilerPass(new AddResolveTargetEntitiesPass())

            ->addCompilerPass(new AddCacheClearCommandsPass())
            ->addCompilerPass(new AddDashboardWidgetsPass())
            ->addCompilerPass(new AddMenuItemFactoriesPass())
            ->addCompilerPass(new AddViewWidgetsPass())
            ->addCompilerPass(new CreateControllersPass())
            ->addCompilerPass(new CreateMetadataPass())
            ->addCompilerPass(new CreateSecurityConfigurationsPass())
            ->addCompilerPass(new ReplaceTranslatableSubscriberPass());
    }
}
