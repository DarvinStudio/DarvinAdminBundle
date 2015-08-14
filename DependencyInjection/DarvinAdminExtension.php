<?php

namespace Darvin\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DarvinAdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
//        $configuration = new Configuration();
//        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('breadcrumbs.yml');
        $loader->load('cache.yml');
        $loader->load('configuration.yml');
        $loader->load('controller.yml');
        $loader->load('flash.yml');
        $loader->load('form.yml');
        $loader->load('menu.yml');
        $loader->load('metadata.yml');
        $loader->load('route.yml');
        $loader->load('stringifier.yml');
        $loader->load('view.yml');
    }
}
