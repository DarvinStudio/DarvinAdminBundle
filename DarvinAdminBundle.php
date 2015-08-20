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

use Darvin\AdminBundle\DependencyInjection\Compiler\ControllerPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\MenuPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\MetadataPass;
use Darvin\AdminBundle\DependencyInjection\Compiler\ViewWidgetGeneratorPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Admin bundle
 */
class DarvinAdminBundle extends Bundle
{
    const VERSION = 0.01;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ControllerPass(), PassConfig::TYPE_BEFORE_REMOVING)
            ->addCompilerPass(new MenuPass(), PassConfig::TYPE_BEFORE_REMOVING)
            ->addCompilerPass(new MetadataPass())
            ->addCompilerPass(new ResolveDefinitionTemplatesPass(), PassConfig::TYPE_BEFORE_REMOVING)
            ->addCompilerPass(new ViewWidgetGeneratorPass());
    }
}
