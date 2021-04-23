<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\DependencyInjection;

use Darvin\AdminBundle\Dashboard\DashboardWidgetInterface;
use Darvin\AdminBundle\Entity\LogEntry;
use Darvin\AdminBundle\Menu\ItemFactoryInterface;
use Darvin\AdminBundle\Security\User\Roles;
use Darvin\AdminBundle\View\Widget\WidgetInterface;
use Darvin\ConfigBundle\Entity\ParameterEntity;
use Darvin\ImageBundle\DarvinImageBundle;
use Darvin\Utils\DependencyInjection\ConfigInjector;
use Darvin\Utils\DependencyInjection\ConfigLoader;
use Darvin\Utils\DependencyInjection\ExtensionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DarvinAdminExtension extends Extension implements PrependExtensionInterface
{
    public const TAG_DASHBOARD_WIDGET  = 'darvin_admin.dashboard_widget';
    public const TAG_MENU_ITEM_FACTORY = 'darvin_admin.menu_item_factory';
    public const TAG_VIEW_WIDGET       = 'darvin_admin.view_widget';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->registerForAutoconfiguration(DashboardWidgetInterface::class)->addTag(self::TAG_DASHBOARD_WIDGET);
        $container->registerForAutoconfiguration(ItemFactoryInterface::class)->addTag(self::TAG_MENU_ITEM_FACTORY);
        $container->registerForAutoconfiguration(WidgetInterface::class)->addTag(self::TAG_VIEW_WIDGET);

        $this->mergeSectionConfigs($configs);

        (new ConfigInjector($container))->inject($this->processConfiguration(new Configuration(), $configs), $this->getAlias());

        (new ConfigLoader($container, __DIR__.'/../Resources/config/services'))->load([
            'ace_editor',
            'breadcrumbs',
            'cache/clear/twig_extension',
            'ckeditor',
            'configuration',
            'cookie',
            'crud',
            'dashboard',
            'dropzone',
            'entity_namer',
            'form',
            'homepage',
            'locale',
            'menu',
            'metadata',
            'page',
            'pagination',
            'push',
            'route',
            'search',
            'security',
            'slug_suffix',
            'toolbar',
            'twig',
            'uploader',
            'view',

            'cache/clear/clearer' => ['callback' => function () use ($config): bool {
                if (!$config['cache']['clear']['enabled']) {
                    return false;
                }
                foreach ($config['cache']['clear']['sets'] as $setAttr) {
                    if ($setAttr['enabled']) {
                        foreach ($setAttr['commands'] as $commandAttr) {
                            if ($commandAttr['enabled']) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }],
            'cache/clear/list' => ['callback' => function () use ($config): bool {
                if (!$config['cache']['clear']['enabled']
                    || !$config['cache']['clear']['sets']['list']['enabled']
                ) {
                    return false;
                }
                foreach ($config['cache']['clear']['sets']['list']['commands'] as $commandAttr) {
                    if ($commandAttr['enabled']) {
                        return true;
                    }
                }

                return false;
            }],
            'cache/clear/widget' => ['callback' => function () use ($config): bool {
                if (!$config['cache']['clear']['enabled']
                    || !$config['cache']['clear']['sets']['widget']['enabled']
                ) {
                    return false;
                }
                foreach ($config['cache']['clear']['sets']['widget']['commands'] as $commandAttr) {
                    if ($commandAttr['enabled']) {
                        return true;
                    }
                }

                return false;
            }],

            'dev/metadata'              => ['env' => 'dev'],
            'dev/translation_generator' => ['env' => 'dev'],
            'dev/view'                  => ['env' => 'dev'],

            'error' => ['env' => 'prod'],

            'prod/cache' => ['env' => 'prod'],

            'translation' => ['bundle' => 'LexikTranslationBundle'],
        ]);

        (new ConfigInjector($container))->inject($this->processConfiguration(new Configuration(), $configs), $this->getAlias());
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $config = $container->getParameterBag()->resolveValue($this->processConfiguration(
            new Configuration(),
            $container->getParameterBag()->resolveValue($container->getExtensionConfig($this->getAlias()))
        ));

        $container->setParameter('darvin_admin.default_locale', $config['default_locale']);
        $container->setParameter('darvin_admin.locales', $config['locales']);
        $container->setParameter('darvin_admin.tmp_dir', '%kernel.project_dir%/var/tmp/darvin/admin');

        (new ExtensionConfigurator($container, __DIR__.'/../Resources/config/app'))->configure([
            'a2lix_translation_form',
            'bazinga_js_translation',
            'darvin_admin',
            'darvin_content',
            DarvinImageBundle::MAJOR_VERSION >= 8 ? 'darvin_file' : 'darvin_image',
            'darvin_user',
            'fm_elfinder',
            'hwi_oauth',
            'fos_ck_editor',
            'lexik_translation',
            'liip_imagine',
            'oneup_uploader',
            'twig',
        ]);

        $sections = [
            ParameterEntity::class => [
                'alias' => 'configuration',
            ],
            LogEntry::class => [
                'alias'  => 'log',
                'config' => '@DarvinAdminBundle/Resources/config/admin/log.yaml',
            ],
        ];

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['LexikTranslationBundle'])) {
            $sections['Lexik\Bundle\TranslationBundle\Entity\Translation'] = [
                'config' => '@DarvinAdminBundle/Resources/config/admin/translation.yaml',
            ];
        }

        $container->prependExtensionConfig($this->getAlias(), [
            'sections'    => $sections,
            'permissions' => [
                Roles::ROLE_COMMON_ADMIN => [
                    'default' => true,
                ],
                Roles::ROLE_SUPER_ADMIN => [
                    'default' => true,
                ],
            ],
            'menu' => [
                'groups' => [
                    'modules' => [
                        'position' => 500,
                    ],
                    'seo' => [
                        'position' => 500,
                    ],
                ],
            ],
            'form' => [
                'default_field_options' => [
                    CheckboxType::class => [
                        'required' => false,
                    ],
                    DateType::class => [
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy',
                        'html5'  => false,
                    ],
                    DateTimeType::class => [
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy HH:mm',
                        'html5'  => false,
                    ],
                    TimeType::class => [
                        'widget' => 'single_text',
                    ],
                ],
            ],
            'cache' => [
                'clear' => [
                    'sets' => [],
                ],
            ],
        ]);
    }

    /**
     * @param array $configs Section configurations
     */
    private function mergeSectionConfigs(array &$configs): void
    {
        foreach ($configs as $configKey => $config) {
            if (!isset($config['sections'])) {
                continue;
            }
            foreach ($config['sections'] as $sectionEntity => $section) {
                foreach ($configs as $otherConfigKey => $otherConfig) {
                    if (!isset($otherConfig['sections']) || $otherConfigKey === $configKey) {
                        continue;
                    }
                    foreach ($otherConfig['sections'] as $otherSectionEntity => $otherSection) {
                        if (($otherSectionEntity === $sectionEntity)
                            || (isset($section['alias']) && isset($otherSection['alias']) && $otherSection['alias'] === $section['alias'])
                        ) {
                            $configs[$configKey]['sections'][$sectionEntity] = array_merge($section, $otherSection);
                            unset($configs[$otherConfigKey]['sections'][$otherSectionEntity]);
                        }
                    }
                }
            }
        }
    }
}
