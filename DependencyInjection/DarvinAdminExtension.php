<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
    private const FIREWALL_NAME = 'admin_area';

    /**
     * @var bool
     */
    private $showErrorPages;

    /**
     * Extension constructor.
     */
    public function __construct()
    {
        $this->showErrorPages = false;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $showErrorPages = $this->showErrorPages;

        $this->mergeSectionConfigs($configs);

        (new ConfigInjector($container))->inject($this->processConfiguration(new Configuration(), $configs), $this->getAlias());

        (new ConfigLoader($container, __DIR__.'/../Resources/config'))->load([
            'ace_editor',
            'breadcrumbs',
            'ckeditor',
            'configuration',
            'crud',
            'dashboard',
            'dropzone',
            'entity_namer',
            'form',
            'locale',
            'menu',
            'metadata',
            'page',
            'route',
            'search',
            'security',
            'slug_suffix',
            'twig',
            'uploader',
            'view',

            'dev/metadata'              => ['env' => 'dev'],
            'dev/translation_generator' => ['env' => 'dev'],
            'dev/view'                  => ['env' => 'dev'],

            'prod/cache'                => ['env' => 'prod'],

            'translation'               => ['bundle' => 'LexikTranslationBundle'],

            'error'                     => ['callback' => function () use ($showErrorPages) {
                return $showErrorPages;
            }],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $container->setParameter('darvin_admin.tmp_dir', sprintf('%s/darvin/admin', sys_get_temp_dir()));

        if (!$container->getParameter('kernel.debug')) {
            foreach ($container->getExtensionConfig('security') as $config) {
                if (isset($config['firewalls'][self::FIREWALL_NAME])) {
                    $firewallConfig = $config['firewalls'][self::FIREWALL_NAME];

                    $this->showErrorPages = isset($firewallConfig['pattern']) && '^/' !== $firewallConfig['pattern'];
                }
            }
        }

        (new ExtensionConfigurator($container, __DIR__.'/../Resources/config/app'))->configure([
            'a2lix_translation_form',
            'bazinga_js_translation',
            'darvin_content',
            'darvin_image',
            'fm_elfinder',
            'hwi_oauth',
            'fos_ck_editor',
            'lexik_translation',
            'liip_imagine',
            'oneup_uploader',
            'twig',
        ]);

        if ($container->hasExtension('darvin_user')) {
            $container->prependExtensionConfig('darvin_user', [
                'roles' => array_fill_keys(Roles::getRoles(), null),
            ]);
        }

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
                    ],
                    DateTimeType::class => [
                        'widget' => 'single_text',
                        'format' => 'dd.MM.yyyy HH:mm',
                    ],
                    TimeType::class => [
                        'widget' => 'single_text',
                    ],
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
