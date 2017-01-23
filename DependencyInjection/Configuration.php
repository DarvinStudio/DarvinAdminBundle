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

use Darvin\AdminBundle\Menu\Item;
use Liip\ImagineBundle\Command\RemoveCacheCommand;
use Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand;
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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('darvin_admin');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->append($this->addCKEditorNode())
                ->append($this->addMenuNode())
                ->append($this->addSectionsNode())
                ->arrayNode('cache_clear_command_classes')
                    ->prototype('scalar')->end()
                    ->defaultValue([
                        CacheClearCommand::class,
                        RemoveCacheCommand::class,
                    ])
                ->end()
                ->scalarNode('custom_logo')->defaultNull()->end()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->arrayNode('entity_override')->prototype('scalar')->end()->end()
                ->arrayNode('locales')->prototype('scalar')->end()->cannotBeEmpty()->isRequired()->end()
                ->integerNode('search_query_min_length')->min(1)->defaultValue(3)->end()
                ->scalarNode('translations_model_dir')->defaultValue('Resources/config/translations')->end()
                ->integerNode('upload_max_size_mb')->defaultValue(2)->end()
                ->scalarNode('visual_assets_path')->defaultValue('bundles/darvinadmin')->end()
                ->scalarNode('yandex_translate_api_key')->defaultNull()->end()
                ->arrayNode('project')->isRequired()
                    ->children()
                        ->scalarNode('title')->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('url')->cannotBeEmpty()->isRequired();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addCKEditorNode()
    {
        $rootNode = (new TreeBuilder())->root('ckeditor');
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('plugin_filename')->defaultValue('plugin.js')->end()
                ->scalarNode('plugins_path')->defaultValue('/bundles/darvinadmin/scripts/ckeditor/plugins');

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addSectionsNode()
    {
        $rootNode = (new TreeBuilder())->root('sections');
        $rootNode
            ->prototype('array')->canBeDisabled()
                ->children()
                    ->scalarNode('alias')->defaultNull()->end()
                    ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('config')->defaultNull();

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addMenuNode()
    {
        $rootNode = (new TreeBuilder())->root('menu');
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('groups')
                    ->prototype('array')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->integerNode('position')->defaultNull()->end()
                            ->arrayNode('colors')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('main')->defaultNull()->end()
                                    ->scalarNode('sidebar')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('icons')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('main')->defaultValue(Item::DEFAULT_MAIN_ICON)->end()
                                    ->scalarNode('sidebar')->defaultValue(Item::DEFAULT_SIDEBAR_ICON)->end();

        return $rootNode;
    }
}
