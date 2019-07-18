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

use Darvin\AdminBundle\Menu\Item;
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
                ->integerNode('upload_max_size_mb')->defaultValue(2)->min(1)->end()
                ->scalarNode('yandex_translate_api_key')->defaultNull()->end()
                ->arrayNode('dashboard')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('blacklist')->prototype('scalar')->cannotBeEmpty();

        return $builder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createCKEditorNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('ckeditor'))->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
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
                    ->append($this->createEntityPermissionsNode());

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createDefaultPermissionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('default'))->getRootNode();
        $root->addDefaultsIfNotSet();
        $root->beforeNormalization()->always(function ($value) {
            if (is_bool($value)) {
                return array_fill_keys(Permission::getAllPermissions(), $value);
            }

            return $value;
        });

        $builder = $root->children();

        foreach (Permission::getAllPermissions() as $permission) {
            $builder->booleanNode($permission)->defaultFalse();
        }

        return $root;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function createEntityPermissionsNode(): ArrayNodeDefinition
    {
        $root = (new TreeBuilder('entities'))->getRootNode();
        $root->useAttributeAsKey('entity');
        $root->beforeNormalization()->always(function ($value) {
            if (is_bool($value)) {
                return array_fill_keys(Permission::getAllPermissions(), $value);
            }

            return $value;
        });

        $builder = $root->prototype('array')->children();

        foreach (Permission::getAllPermissions() as $permission) {
            $builder->booleanNode($permission)->defaultFalse();
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
                            ->integerNode('position')->defaultNull()->end()
                            ->scalarNode('associated_object')->defaultNull()->end()
                            ->arrayNode('colors')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('main')->defaultNull()->end()
                                    ->scalarNode('sidebar')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('icons')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('main')->defaultValue(Item::DEFAULT_MAIN_ICON)->cannotBeEmpty()->end()
                                    ->scalarNode('sidebar')->defaultValue(Item::DEFAULT_SIDEBAR_ICON)->cannotBeEmpty();

        return $root;
    }
}
