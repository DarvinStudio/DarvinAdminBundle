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
use Darvin\AdminBundle\Security\User\Roles;
use Darvin\ConfigBundle\Entity\ParameterEntity;
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
        $this->mergeSectionConfigs($configs);

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
            'locale',
            'menu',
            'metadata',
            'route',
            'search',
            'security',
            'slug_suffix',
            'translation_generator',
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

        if (isset($bundles['DarvinUserBundle'])) {
            $container->prependExtensionConfig('darvin_user', [
                'roles' => Roles::getRoles(),
            ]);
        }

        $sections = [
            [
                'alias'  => 'configuration',
                'entity' => ParameterEntity::PARAMETER_ENTITY_CLASS,
            ],
            [
                'alias'  => 'log',
                'entity' => LogEntry::LOG_ENTRY_CLASS,
                'config' => '@DarvinAdminBundle/Resources/config/admin/log.yml',
            ],
        ];

        if (isset($bundles['LexikTranslationBundle'])) {
            $sections[] = [
                'entity' => 'Lexik\Bundle\TranslationBundle\Entity\Translation',
                'config' => '@DarvinAdminBundle/Resources/config/admin/translation.yml',
            ];
        }

        $container->prependExtensionConfig('darvin_admin', [
            'sections' => $sections,
            'menu'     => [
                'groups' => [
                    [
                        'name'   => 'modules',
                        'colors' => [
                            'main'    => '#ff9a16',
                            'sidebar' => '#ffe86d',
                        ],
                        'icons' => [
                            'main'    => 'bundles/darvinadmin/images/admin/modules_main.png',
                            'sidebar' => 'bundles/darvinadmin/images/admin/modules_sidebar.png',
                        ],
                    ],
                    [
                        'name'     => 'pages',
                        'position' => 1,
                        'colors'   => [
                            'main'    => '#649ea6',
                            'sidebar' => '#9befe2',
                        ],
                        'icons' => [
                            'main'    => 'bundles/darvinadmin/images/admin/pages_main.png',
                            'sidebar' => 'bundles/darvinadmin/images/admin/pages_sidebar.png',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array $configs Section configurations
     */
    private function mergeSectionConfigs(array &$configs)
    {
        foreach ($configs as $configKey => $config) {
            if (!isset($config['sections'])) {
                continue;
            }
            foreach ($config['sections'] as $sectionKey => $section) {
                if (!isset($section['alias']) && !isset($section['entity'])) {
                    continue;
                }
                foreach ($configs as $otherConfigKey => $otherConfig) {
                    if (!isset($otherConfig['sections']) || $otherConfigKey === $configKey) {
                        continue;
                    }
                    foreach ($otherConfig['sections'] as $otherSectionKey => $otherSection) {
                        if ((isset($section['alias']) && isset($otherSection['alias']) && $otherSection['alias'] === $section['alias'])
                            || (isset($section['entity']) && isset($otherSection['entity']) && $otherSection['entity'] === $section['entity'])
                        ) {
                            $configs[$configKey]['sections'][$sectionKey] = array_merge($section, $otherSection);
                            unset($configs[$otherConfigKey]['sections'][$otherSectionKey]);
                        }
                    }
                }
            }
        }
    }
}
