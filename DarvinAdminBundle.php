<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle;

use Darvin\AdminBundle\DependencyInjection\Compiler\AddCacheClearCommandsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddDashboardWidgetsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddMenuItemFactoriesPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\AddViewWidgetsPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\CreateMetadataPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\ReplaceAutoFormListenerPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\ReplaceFormObjectInfoPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\SwitchFormManipulatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Admin bundle
 */
class DarvinAdminBundle extends Bundle
{
    public const VERSION = '6.4';

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new AddCacheClearCommandsPass())
            ->addCompilerPass(new AddDashboardWidgetsPass())
            ->addCompilerPass(new AddMenuItemFactoriesPass())
            ->addCompilerPass(new AddViewWidgetsPass())
            ->addCompilerPass(new CreateMetadataPass())
            ->addCompilerPass(new ReplaceAutoFormListenerPass())
            ->addCompilerPass(new ReplaceFormObjectInfoPass())
            ->addCompilerPass(new SwitchFormManipulatorPass());
    }
}
