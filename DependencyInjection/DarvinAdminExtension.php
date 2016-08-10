<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection;

use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\Utils\DependencyInjection\ConfigInjector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DarvinAdminExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        (new ConfigInjector())->inject($this->processConfiguration(new Configuration(), $configs), $container, $this->getAlias());

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach ([
            'asset/provider',
            'breadcrumbs',
            'cache',
            'ckeditor',
            'configuration',
            'crud',
            'dashboard',
            'dropzone',
            'entity_namer',
            'form',
            'image',
            'menu',
            'metadata',
            'route',
            'search',
            'security',
            'slug_suffix',
            'twig',
            'uploader',
            'view',
        ] as $resource) {
            $loader->load($resource.'.yml');
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['AsseticBundle']) && 'dev' === $container->getParameter('kernel.environment')) {
            $loader->load('asset/compiler.yml');
        }
        if (isset($bundles['LexikTranslationBundle'])) {
            $loader->load('translation.yml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $sections = [
//            [
//                'alias'  => 'image',
//                'entity' => AbstractImage::ABSTRACT_IMAGE_CLASS,
//                'config' => __DIR__.'/../Resources/config/admin/image.yml',
//            ],
            [
                'alias'  => 'log',
                'entity' => LogEntry::LOG_ENTRY_CLASS,
                'config' => __DIR__.'/../Resources/config/admin/log.yml',
            ],
        ];

        if (isset($bundles['LexikTranslationBundle'])) {
            $sections[] = [
                'entity' => 'Lexik\Bundle\TranslationBundle\Entity\Translation',
                'config' => __DIR__.'/../Resources/config/admin/translation.yml',
            ];
        }

        $container->prependExtensionConfig('darvin_admin', [
            'sections' => $sections,
        ]);
    }
}
