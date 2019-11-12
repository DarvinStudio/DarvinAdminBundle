<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata\Configuration;

use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\View\Widget\Widget\BatchDeleteWidget;
use Darvin\AdminBundle\View\Widget\Widget\CopyFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\DeleteFormWidget;
use Darvin\AdminBundle\View\Widget\Widget\EditLinkWidget;
use Darvin\AdminBundle\View\Widget\Widget\ShowLinkWidget;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('root');

        $builder->getRootNode()
            ->children()
                ->append($this->buildMenuNode())
                ->scalarNode('breadcrumbs_route')->defaultValue(AdminRouterInterface::TYPE_EDIT)->end()
                ->arrayNode('children')->prototype('scalar')->end()->end()
                ->booleanNode('index_view_new_form')->defaultFalse()->end()
                ->arrayNode('index_view_row_attr')->prototype('scalar')->end()->end()
                ->arrayNode('joins')->prototype('scalar')->end()->end()
                ->booleanNode('oauth_only')->defaultFalse()->end()
                ->arrayNode('order_by')->prototype('enum')->values(['asc', 'desc'])->end()->end()
                ->arrayNode('searchable_fields')->prototype('scalar')->end()->end()
                ->booleanNode('single_instance')->defaultFalse()->end()
                ->arrayNode('sortable_fields')->prototype('scalar')->end()->end()
                ->arrayNode('route_blacklist')->prototype('scalar')->end()->end()
                ->arrayNode('pagination')->canBeDisabled()
                    ->children()
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
                        ->append($this->buildFormNode('index'))
                        ->append($this->buildFormNode('new'))
                        ->append($this->buildFormNode('edit'))
                        ->append($this->buildFormNode('filter'))
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
                        ->append($this->buildViewNode('index', [
                            BatchDeleteWidget::ALIAS,
                            ShowLinkWidget::ALIAS,
                            EditLinkWidget::ALIAS,
                            CopyFormWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]))
                        ->append($this->buildViewNode('new'))
                        ->append($this->buildViewNode('edit', [
                            ShowLinkWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]))
                        ->append($this->buildViewNode('show', [
                            EditLinkWidget::ALIAS,
                            DeleteFormWidget::ALIAS,
                        ]));

        return $builder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function buildMenuNode(): ArrayNodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $root */
        $root = (new TreeBuilder('menu'))->getRootNode();

        $root->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('group')->defaultNull()->end()
                ->scalarNode('position')->defaultNull()->end()
                ->booleanNode('skip')->defaultFalse();

        return $root;
    }

    /**
     * @param string $form Form name
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function buildFormNode(string $form): ArrayNodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $root */
        $root                      = (new TreeBuilder($form))->getRootNode();
        $normalizeFormTypeCallback = $this->createNormalizeFormTypeCallback();

        $root->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('type')->defaultNull()->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultNull()->beforeNormalization()->ifString()->then($normalizeFormTypeCallback)->end()->end()
                            ->scalarNode('condition')->defaultNull()->end()
                            ->arrayNode('options')->prototype('variable')->end()->end()
                            ->scalarNode('compare_strict')->defaultTrue();

        return $root;
    }

    /**
     * @param string $view                 View type
     * @param array  $defaultActionWidgets Default action widgets
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function buildViewNode(string $view, array $defaultActionWidgets = []): ArrayNodeDefinition
    {
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $root */
        $root = (new TreeBuilder($view))->getRootNode();

        $root->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('template')->defaultNull()->end()
                ->arrayNode('action_widgets')->defaultValue(array_fill_keys($defaultActionWidgets, []))
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
                ->arrayNode('extra_action_widgets')->prototype('array')->prototype('variable')->end()->end()->end()
                ->arrayNode('fields')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function (array $field) {
                                return count($field) + count($field['widget']) > 7;
                            })
                            ->thenInvalid('You must specify callback OR widget OR service but not collection of them.')
                        ->end()
                        ->beforeNormalization()->ifArray()->then(function (array $field) {
                            if (!isset($field['attr'])) {
                                $field['attr'] = [];
                            }
                            foreach (['type', 'size', 'exact_size'] as $name) {
                                $attr = sprintf('data-%s', str_replace('_', '-', $name));

                                if (!isset($field['attr'][$attr]) && isset($field[$name]) && null !== $field[$name]) {
                                    $field['attr'][$attr] = $field[$name];
                                }
                            }

                            return $field;
                        })->end()
                        ->children()
                            ->scalarNode('type')->defaultNull()->end()
                            ->scalarNode('size')->defaultNull()->end()
                            ->scalarNode('exact_size')->defaultNull()->end()
                            ->scalarNode('condition')->defaultNull()->end()
                            ->arrayNode('attr')->normalizeKeys(false)->prototype('scalar')->end()->end()
                            ->arrayNode('widget')->useAttributeAsKey('alias')->prototype('array')->prototype('variable')->end()->end()
                                ->beforeNormalization()->ifString()->then(function ($alias) {
                                    return [
                                        $alias => [],
                                    ];
                                })->end()
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

        return $root;
    }

    /**
     * @return callable
     */
    private function createNormalizeFormTypeCallback(): callable
    {
        return function (string $type) {
            if (false !== strpos($type, '\\')) {
                return $type;
            }

            $class = sprintf('Symfony\Component\Form\Extension\Core\Type\%sType', ucfirst($type));

            return class_exists($class) ? $class : $type;
        };
    }
}
