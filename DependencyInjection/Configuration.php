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

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('darvin_admin');

        $root = $builder->getRootNode();
        $root
            ->children()
                ->append($this->createAssetsNode())
                ->append($this->createCacheNode())
                ->append($this->createCKEditorNode())
                ->append($this->createFormNode())
                ->append($this->createMenuNode())
                ->append($this->createPermissionsNode())
                ->append($this->createSectionsNode())
                ->scalarNode('frontend_path')->defaultValue('bundles/darvinadmin')->cannotBeEmpty()->end()
                ->arrayNode('locales')->prototype('scalar')->cannotBeEmpty()->end()->isRequired()->requiresAtLeastOneElement()->end()
                ->scalarNode('logo')->defaultNull()->end()
                ->scalarNode('project_title')->isRequired()->cannotBeEmpty()->end()
                ->integerNode('search_query_min_length')->defaultValue(3)->min(1)->end()
                ->scalarNode('translations_model_dir')->defaultValue('Resources/config/translations')->cannotBeEmpty()->end()
                ->scalarNode('upload_max_size_mb')->defaultValue(2)->cannotBeEmpty()->end()
                ->scalarNode('yandex_translate_api_key')->defaultNull()->end()
                ->arrayNode('dashboard')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('blacklist')->prototype('scalar')->cannotBeEmpty();

        return $builder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createAssetsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('assets'))->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('scripts')->prototype('scalar')->cannotBeEmpty()->end()->end()
                ->arrayNode('styles')->prototype('scalar')->cannotBeEmpty();

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createCacheNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('cache'))->getRootNode();
        $root
            ->children()
                ->arrayNode('clear')->canBeDisabled()
                    ->children()
                        ->arrayNode('sets')->useAttributeAsKey('name')
                            ->prototype('array')->canBeDisabled()
                                ->children()
                                    ->booleanNode('clear_on_crud')->defaultFalse()->end()
                                    ->arrayNode('commands')->useAttributeAsKey('alias')
                                        ->prototype('array')->canBeDisabled()
                                            ->children()
                                                ->scalarNode('id')->isRequired()->cannotBeEmpty()
                                                    ->beforeNormalization()->ifString()->then(function (string $id): string {
                                                        return ltrim($id, '@');
                                                    })->end()
                                                ->end()
                                                ->arrayNode('input')->useAttributeAsKey('key')->normalizeKeys(false)->prototype('scalar')->end()->end()
                                            ->end()
                                            ->beforeNormalization()->ifString()->then(function (string $id): array {
                                                return [
                                                    'id' => $id,
                                                ];
                                            });

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createCKEditorNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('ckeditor'))->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('apply_contents_css')->defaultFalse()->end()
                ->scalarNode('plugin_filename')->defaultValue('plugin.js')->cannotBeEmpty()->end()
                ->scalarNode('plugins_path')->defaultValue('/bundles/darvinadmin/scripts/ckeditor/plugins')->cannotBeEmpty();

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createFormNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('form'))->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('default_field_options')
                    ->prototype('array')
                        ->prototype('variable');

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createPermissionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('permissions'))->getRootNode();
        $root->useAttributeAsKey('role')
            ->prototype('array')
                ->children()
                    ->append($this->createDefaultPermissionsNode())
                    ->append($this->createSubjectPermissionsNode())
                ->end()
                ->beforeNormalization()->ifArray()->then(function (array $permissions) {
                    if (!isset($permissions['default']) || null === $permissions['default']) {
                        $permissions['default'] = false;
                    }
                    if (is_bool($permissions['default'])) {
                        $permissions['default'] = array_fill_keys(Permission::getAllPermissions(), $permissions['default']);
                    }
                    foreach ($permissions['default'] as $permission => $granted) {
                        if (0 !== strpos($permission, Permission::PREFIX)) {
                            $permissions['default'][Permission::PREFIX.$permission] = $granted;

                            unset($permissions['default'][$permission]);
                        }
                    }
                    if (!isset($permissions['subjects'])) {
                        $permissions['subjects'] = [];
                    }
                    foreach ($permissions['subjects'] as $subject => &$subjectPermissions) {
                        if (null === $subjectPermissions) {
                            $subjectPermissions = $permissions['default'];
                        }
                        if (is_bool($subjectPermissions)) {
                            $subjectPermissions = array_fill_keys(Permission::getAllPermissions(), $subjectPermissions);
                        }
                        foreach ($subjectPermissions as $permission => $granted) {
                            if (0 !== strpos($permission, Permission::PREFIX)) {
                                $subjectPermissions[Permission::PREFIX.$permission] = $granted;

                                unset($subjectPermissions[$permission]);
                            }
                        }

                        $subjectPermissions = array_merge($permissions['default'], $subjectPermissions);
                    }

                    unset($subjectPermissions);

                    return $permissions;
                });

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createDefaultPermissionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('default'))->getRootNode();
        $root->addDefaultsIfNotSet();

        $builder = $root->children();

        foreach (Permission::getAllPermissions() as $permission) {
            $builder->booleanNode($permission);
        }

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createSubjectPermissionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('subjects'))->getRootNode();
        $root->useAttributeAsKey('subject');
        $root->validate()->ifTrue(function (array $subjects) {
            foreach (array_keys($subjects) as $subject) {
                if (false !== strpos($subject, '\\') && !class_exists($subject) && !interface_exists($subject)) {
                    throw new \InvalidArgumentException(sprintf('Class or interface "%s" does not exist.', $subject));
                }
            }

            return false;
        })->thenInvalid('');

        $builder = $root->prototype('array')->children();

        foreach (Permission::getAllPermissions() as $permission) {
            $builder->booleanNode($permission);
        }

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createSectionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('sections'))->getRootNode();
        $root->useAttributeAsKey('entity')
            ->prototype('array')->canBeDisabled()
                ->beforeNormalization()->ifString()->then(function (string $config) {
                    return [
                        'config' => $config,
                    ];
                })->end()
                ->children()
                    ->scalarNode('alias')->defaultNull()->end()
                    ->scalarNode('config')->defaultNull();

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createMenuNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('menu'))->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('groups')->useAttributeAsKey('name')
                    ->prototype('array')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('associated_object')->defaultNull()->end()
                            ->integerNode('position')->defaultNull()->end()
                            ->arrayNode('separators')->useAttributeAsKey('name')
                                ->prototype('array')->addDefaultsIfNotSet()
                                    ->children()
                                        ->integerNode('position')->isRequired()->end()
                                    ->end()
                                    ->beforeNormalization()->always(function ($separator) {
                                        if (!is_array($separator)) {
                                            $separator = [
                                                'position' => $separator,
                                            ];
                                        }

                                        return $separator;
                                    });

        return $root;
    }
}
