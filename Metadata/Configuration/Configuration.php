<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata\Configuration;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\View\Widget\Widget\CopyFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\DeleteFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\EditLinkWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $container = $this->container;

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->append($this->addMenuNode())
                ->scalarNode('breadcrumbs_entity_route')->defaultValue(AdminRouter::TYPE_EDIT)->end()
                ->arrayNode('child_entities')->prototype('scalar')->end()->end()
                ->arrayNode('disabled_routes')->prototype('scalar')->end()->end()
                ->booleanNode('index_view_new_form')->defaultFalse()->end()
                ->arrayNode('index_view_row_attr')->prototype('scalar')->end()->end()
                ->arrayNode('joins')->prototype('scalar')->end()->end()
                ->arrayNode('order_by')->prototype('enum')->values(['asc', 'desc'])->end()->end()
                ->arrayNode('searchable_fields')->prototype('scalar')->end()->end()
                ->arrayNode('sortable_fields')->prototype('scalar')->end()->end()
                ->arrayNode('pagination')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->integerNode('items')->defaultValue(10)->min(1)->end()
                    ->end()
                ->end()
                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addFormNode('index'))
                        ->append($this->addFormNode('new'))
                        ->append($this->addFormNode('edit'))
                        ->append($this->addFormNode('filter'))
                    ->end()
                ->end()
                ->arrayNode('sorter')
                    ->validate()
                        ->ifTrue(function ($v) use ($container) {
                            return !method_exists($container->get($v['id']), $v['method']);
                        })
                        ->thenInvalid('Service does not have method %s.')
                    ->end()
                    ->children()
                        ->scalarNode('id')
                            ->defaultNull()
                            ->validate()
                                ->ifTrue(function ($v) use ($container) {
                                    return !$container->has($v);
                                })
                                ->thenInvalid('Service %s does not exist.')
                            ->end()
                        ->end()
                        ->scalarNode('method')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('view')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addViewNode('index', [
                            ShowLinkWidget::ALIAS,
                            EditLinkWidget::ALIAS,
                            CopyFormWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]))
                        ->append($this->addViewNode('new'))
                        ->append($this->addViewNode('edit', [
                            ShowLinkWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]))
                        ->append($this->addViewNode('show', [
                            EditLinkWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]))
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addMenuNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('menu');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('colors')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('main')->defaultNull()->end()
                        ->scalarNode('sidebar')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('icons')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('main')->defaultValue(Item::DEFAULT_MAIN_ICON)->end()
                        ->scalarNode('sidebar')->defaultValue(Item::DEFAULT_SIDEBAR_ICON)->end()
                    ->end()
                ->end()
                ->scalarNode('group')->defaultNull()->end()
                ->scalarNode('position')->defaultNull()->end()
                ->booleanNode('skip')->defaultFalse()->end()
            ->end();

        return $rootNode;
    }

    /**
     * @param string $form Form name
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addFormNode($form)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($form);

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('type')->defaultNull()->end()
                ->arrayNode('field_groups')
                    ->prototype('array')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')->defaultNull()->end()
                                ->arrayNode('options')->prototype('variable')->end()->end()
                                ->scalarNode('compare_strict')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultNull()->end()
                            ->arrayNode('options')->prototype('variable')->end()->end()
                            ->scalarNode('compare_strict')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    /**
     * @param string $view                 View type
     * @param array  $defaultActionWidgets Default action widgets
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addViewNode($view, array $defaultActionWidgets = [])
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($view);

        $container = $this->container;

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('action_widgets')
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                    ->defaultValue(array_fill_keys($defaultActionWidgets, []))
                ->end()
                ->scalarNode('template')->defaultNull()->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function ($v) {
                                return count($v) > 1;
                            })
                            ->thenInvalid('You must specify callback OR widget OR service but not collection of them.')
                        ->end()
                        ->children()
                            ->arrayNode('callback')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !method_exists($v['class'], $v['method']);
                                    })
                                    ->thenInvalid('Method does not exist %s.')
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        $methodReflection = new \ReflectionMethod($v['class'], $v['method']);

                                        return !$methodReflection->isStatic();
                                    })
                                    ->thenInvalid('Method is not static %s.')
                                ->end()
                                ->children()
                                    ->scalarNode('class')
                                        ->validate()
                                            ->ifTrue(function ($v) {
                                                return !class_exists($v);
                                            })
                                            ->thenInvalid('Class %s does not exist.')
                                        ->end()
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('method')->cannotBeEmpty()->isRequired()->end()
                                    ->arrayNode('options')->prototype('variable')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('widget')
                                ->children()
                                    ->scalarNode('alias')->cannotBeEmpty()->isRequired()->end()
                                    ->arrayNode('options')->prototype('variable')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('service')
                                ->validate()
                                    ->ifTrue(function ($v) use ($container) {
                                        return !method_exists($container->get($v['id']), $v['method']);
                                    })
                                    ->thenInvalid('Service does not have method %s.')
                                ->end()
                                ->children()
                                    ->scalarNode('id')
                                        ->validate()
                                            ->ifTrue(function ($v) use ($container) {
                                                return !$container->has($v);
                                            })
                                            ->thenInvalid('Service %s does not exist.')
                                        ->end()
                                        ->cannotBeEmpty()
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('method')->cannotBeEmpty()->isRequired()->end()
                                    ->arrayNode('options')->prototype('variable')->end()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }
}
