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
                ->arrayNode('cache_clear_command_classes')
                    ->prototype('scalar')->end()
                    ->defaultValue([
                        'Liip\ImagineBundle\Command\RemoveCacheCommand',
                        'Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand',
                    ])
                ->end()
                ->booleanNode('debug')->defaultFalse()->end()
                ->arrayNode('locales')->prototype('scalar')->end()->cannotBeEmpty()->isRequired()->end()
                ->integerNode('upload_max_size_mb')->defaultValue(2)->end()
                ->scalarNode('visual_assets_path')->defaultValue('bundles/darvinadmin')->end()
                ->scalarNode('yandex_translate_api_key')->defaultNull()->end()
                ->arrayNode('project')
                    ->isRequired()
                    ->children()
                        ->scalarNode('title')->cannotBeEmpty()->isRequired()->end()
                        ->scalarNode('url')->cannotBeEmpty()->isRequired()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addCKEditorNode()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('ckeditor');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('plugin_filename')->defaultValue('plugin.js')->end()
                ->scalarNode('plugins_path')->defaultValue('/bundles/darvinadmin/scripts/ckeditor/plugins')->end()
            ->end();

        return $rootNode;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addMenuNode()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('menu');
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('groups')->defaultValue([
                    'pages'        => [
                        'position' => 1,
                        'colors'   => [
                            'main'    => '#649ea6',
                            'sidebar' => '#9befe2',
                        ],
                        'icons'    => [
                            'main'    => 'bundles/darvinadmin/images/admin/pages_main.png',
                            'sidebar' => 'bundles/darvinadmin/images/admin/pages_sidebar.png',
                        ],
                    ],
                    'publications' => [],
                ])->prototype('array')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('position')->defaultNull()->end()
                        ->arrayNode('colors')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('main')->defaultNull()->end()
                                ->scalarNode('sidebar')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('icons')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('main')->defaultNull()->end()
                                ->scalarNode('sidebar')->defaultNull()->end();

        return $rootNode;
    }
}
