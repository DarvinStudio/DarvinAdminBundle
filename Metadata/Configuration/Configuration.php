<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata\Configuration;

use Darvin\AdminBundle\Menu\Item;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\View\Widget\Widget\BatchDeleteWidget;
use Darvin\AdminBundle\View\Widget\Widget\CopyFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\DeleteFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\EditLinkWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('root');
        $rootNode
            ->validate()
                ->ifTrue(function ($v) {
                    return count(array_intersect_key($v['field_blacklist'], $v['field_whitelist'])) > 0;
                })
                ->thenInvalid('Same role cannot be in field blacklist and whitelist simultaneously.')
            ->end()
            ->children()
                ->append($this->addMenuNode())
                ->scalarNode('breadcrumbs_route')->defaultValue(AdminRouterInterface::TYPE_EDIT)->end()
                ->arrayNode('children')->prototype('scalar')->end()->end()
                ->booleanNode('index_view_new_form')->defaultFalse()->end()
                ->arrayNode('index_view_row_attr')->prototype('scalar')->end()->end()
                ->arrayNode('joins')->prototype('scalar')->end()->end()
                ->booleanNode('oauth_only')->defaultFalse()->end()
                ->arrayNode('order_by')->prototype('enum')->values(['asc', 'desc'])->end()->end()
                ->arrayNode('searchable_fields')->prototype('scalar')->end()->end()
                ->arrayNode('sortable_fields')->prototype('scalar')->end()->end()
                ->arrayNode('route_blacklist')->prototype('scalar')->end()->defaultValue([
                    AdminRouterInterface::TYPE_COPY,
                ])->end()
                ->arrayNode('field_blacklist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('field_whitelist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('pagination')->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->integerNode('items')->defaultValue(50)->min(1)->end()
                    ->end()
                ->end()
                ->arrayNode('sorter')
                    ->children()
                        ->scalarNode('id')->defaultNull()->end()
                        ->scalarNode('method')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('form')->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always(function ($v) {
                            if (!isset($v['new']) && isset($v['edit'])) {
                                $v['new'] = $v['edit'];
                            }
                            if (!isset($v['edit']) && isset($v['new'])) {
                                $v['edit'] = $v['new'];
                            }

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->append($this->addFormNode('index'))
                        ->append($this->addFormNode('new'))
                        ->append($this->addFormNode('edit'))
                        ->append($this->addFormNode('filter'))
                    ->end()
                ->end()
                ->arrayNode('view')->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always(function ($v) {
                            if (!isset($v['show']) && isset($v['index'])) {
                                $v['show'] = $v['index'];
                            }

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->append($this->addViewNode('index', [
                            BatchDeleteWidget::ALIAS,
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
                        ]));

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addMenuNode()
    {
        $rootNode = (new TreeBuilder())->root('menu');
        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('group')->defaultNull()->end()
                ->scalarNode('position')->defaultNull()->end()
                ->booleanNode('skip')->defaultFalse()->end()
                ->arrayNode('colors')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('main')->defaultNull()->end()
                        ->scalarNode('sidebar')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('icons')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('main')->defaultValue(Item::DEFAULT_MAIN_ICON)->end()
                        ->scalarNode('sidebar')->defaultValue(Item::DEFAULT_SIDEBAR_ICON);

        return $rootNode;
    }

    /**
     * @param string $form Form name
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function addFormNode($form)
    {
        $normalizeFormType = $this->createNormalizeFormTypeClosure();

        $rootNode = (new TreeBuilder())->root($form);
        $rootNode->addDefaultsIfNotSet()
            ->validate()
                ->ifTrue(function ($v) {
                    return count(array_intersect_key($v['field_blacklist'], $v['field_whitelist'])) > 0;
                })
                ->thenInvalid('Same role cannot be in field blacklist and whitelist simultaneously.')
            ->end()
            ->children()
                ->scalarNode('type')->defaultNull()->end()
                ->arrayNode('field_blacklist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('field_whitelist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('field_groups')
                    ->prototype('array')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')->defaultNull()->beforeNormalization()->ifString()->then($normalizeFormType)->end()->end()
                                ->arrayNode('options')->prototype('variable')->end()->end()
                                ->scalarNode('compare_strict')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultNull()->beforeNormalization()->ifString()->then($normalizeFormType)->end()->end()
                            ->arrayNode('options')->prototype('variable')->end()->end()
                            ->scalarNode('compare_strict')->defaultTrue();

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
        $rootNode = (new TreeBuilder())->root($view);
        $rootNode->addDefaultsIfNotSet()
            ->validate()
                ->ifTrue(function ($v) {
                    return count(array_intersect_key($v['field_blacklist'], $v['field_whitelist'])) > 0;
                })
                ->thenInvalid('Same role cannot be in field blacklist and whitelist simultaneously.')
            ->end()
            ->children()
                ->scalarNode('template')->defaultNull()->end()
                ->arrayNode('action_widgets')->defaultValue(array_fill_keys($defaultActionWidgets, []))
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
                ->arrayNode('field_blacklist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('field_whitelist')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function ($v) {
                                return count($v) > 3;
                            })
                            ->thenInvalid('You must specify callback OR widget OR service but not collection of them.')
                        ->end()
                        ->children()
                            ->scalarNode('condition')->defaultNull()->end()
                            ->arrayNode('attr')->prototype('scalar')->end()->end()
                            ->arrayNode('widget')
                                ->children()
                                    ->scalarNode('alias')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')->prototype('variable')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('service')
                                ->children()
                                    ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('method')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')->prototype('variable')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('callback')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !method_exists($v['class'], $v['method']);
                                    })
                                    ->thenInvalid('Method does not exist %s.')
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !(new \ReflectionMethod($v['class'], $v['method']))->isStatic();
                                    })
                                    ->thenInvalid('Method is not static %s.')
                                ->end()
                                ->children()
                                    ->scalarNode('class')->isRequired()->cannotBeEmpty()
                                        ->validate()
                                            ->ifTrue(function ($v) {
                                                return !class_exists($v);
                                            })
                                            ->thenInvalid('Class %s does not exist.')
                                        ->end()
                                    ->end()
                                    ->scalarNode('method')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('options')->prototype('variable');

        return $rootNode;
    }

    /**
     * @return \Closure
     */
    private function createNormalizeFormTypeClosure()
    {
        return function ($type) {
            if (false !== strpos($type, '\\')) {
                return $type;
            }

            $class = sprintf('Symfony\Component\Form\Extension\Core\Type\%sType', ucfirst($type));

            return class_exists($class) ? $class : $type;
        };
    }
}
