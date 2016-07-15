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

use Darvin\AdminBundle\DependencyInjection\Compiler\AddAssetCompilersPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddAssetProvidersPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddCacheClearCommandsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddDashboardWidgetsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddMetadataPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddSecurityConfigurationsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddViewWidgetGeneratorsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\CreateControllersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Admin bundle
 */
class DarvinAdminBundle extends Bundle
{
    const VERSION = '5.0.0';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new AddAssetCompilersPass())
            ->addCompilerPass(new AddAssetProvidersPass())
            ->addCompilerPass(new AddCacheClearCommandsPass())
            ->addCompilerPass(new CreateControllersPass(), PassConfig::TYPE_BEFORE_REMOVING)
            ->addCompilerPass(new AddDashboardWidgetsPass())
            ->addCompilerPass(new AddMetadataPass())
            ->addCompilerPass(new ResolveDefinitionTemplatesPass(), PassConfig::TYPE_BEFORE_REMOVING)
            ->addCompilerPass(new AddSecurityConfigurationsPass())
            ->addCompilerPass(new AddViewWidgetGeneratorsPass());
    }
}
