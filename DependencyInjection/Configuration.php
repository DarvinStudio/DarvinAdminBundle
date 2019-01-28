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
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('darvin_admin');

        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->append($this->addCKEditorNode())
                ->append($this->addFormNode())
                ->append($this->addMenuNode())
                ->append($this->addSectionsNode())
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
                        ->arrayNode('blacklist')->prototype('scalar');

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addCKEditorNode(): NodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = (new TreeBuilder('ckeditor'))->getRootNode();
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('plugin_filename')->defaultValue('plugin.js')->cannotBeEmpty()->end()
                ->scalarNode('plugins_path')->defaultValue('/bundles/darvinadmin/scripts/ckeditor/plugins')->cannotBeEmpty();

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addFormNode(): NodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = (new TreeBuilder('form'))->getRootNode();
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('default_field_options')
                    ->prototype('array')
                        ->prototype('variable');

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addSectionsNode(): NodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = (new TreeBuilder('sections'))->getRootNode();
        $rootNode->useAttributeAsKey('entity')
            ->prototype('array')->canBeDisabled()
                ->children()
                    ->scalarNode('alias')->defaultNull()->end()
                    ->scalarNode('config')->defaultNull();

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addMenuNode(): NodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = (new TreeBuilder('menu'))->getRootNode();
        $rootNode->addDefaultsIfNotSet()
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

        return $rootNode;
    }
}
