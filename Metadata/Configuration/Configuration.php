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

use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\View\WidgetGenerator\CopyFormGenerator;
use Darvin\AdminBundle\View\WidgetGenerator\DeleteFormGenerator;
use Darvin\AdminBundle\View\WidgetGenerator\EditLinkGenerator;
use Darvin\AdminBundle\View\WidgetGenerator\ShowLinkGenerator;
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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('darvin_admin');

        $rootNode
            ->children()
                ->append($this->addMenuNode())
                ->scalarNode('breadcrumbs_entity_route')->defaultValue(AdminRouter::TYPE_EDIT)->end()
                ->arrayNode('child_entities')->prototype('scalar')->end()->end()
                ->arrayNode('disabled_routes')->prototype('scalar')->end()->end()
                ->scalarNode('entity_name')->defaultNull()->end()
                ->booleanNode('index_view_new_form')->defaultFalse()->end()
                ->arrayNode('sortable_fields')->useAttributeAsKey('field')->prototype('scalar')->end()->end()
                ->arrayNode('order_by')
                    ->useAttributeAsKey('property')
                    ->prototype('enum')->values(array('asc', 'desc'))->end()
                ->end()
                ->integerNode('pagination_items')->defaultValue(10)->min(1)->end()
                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addFormNode('index'))
                        ->append($this->addFormNode('new'))
                        ->append($this->addFormNode('edit'))
                        ->append($this->addFormNode('filter'))
                    ->end()
                ->end()
                ->arrayNode('images')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('homepage_menu_icon')->defaultNull()->end()
                        ->scalarNode('left_menu_icon')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('view')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append(
                            $this->addViewNode(
                                'index',
                                array(ShowLinkGenerator::ALIAS, EditLinkGenerator::ALIAS, CopyFormGenerator::ALIAS, DeleteFormGenerator::ALIAS)
                            )
                        )
                        ->append($this->addViewNode('new'))
                        ->append($this->addViewNode('edit', array(ShowLinkGenerator::ALIAS, DeleteFormGenerator::ALIAS)))
                        ->append($this->addViewNode('show', array(EditLinkGenerator::ALIAS, DeleteFormGenerator::ALIAS)))
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
                ->scalarNode('color')->defaultNull()->end()
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
                    ->useAttributeAsKey('group')
                    ->prototype('array')
                        ->useAttributeAsKey('field')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')->defaultNull()->end()
                                ->arrayNode('options')->prototype('variable')->end()->end()
                                ->scalarNode('strict_comparison')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->useAttributeAsKey('field')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultNull()->end()
                            ->arrayNode('options')->prototype('variable')->end()->end()
                            ->scalarNode('strict_comparison')->defaultTrue()->end()
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
    private function addViewNode($view, array $defaultActionWidgets = array())
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($view);

        $container = $this->container;

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('action_widgets')
                    ->prototype('scalar')->end()
                    ->defaultValue($defaultActionWidgets)
                ->end()
                ->scalarNode('template')->defaultNull()->end()
                ->arrayNode('fields')
                    ->useAttributeAsKey('field')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function ($v) {
                                return count($v) > 1;
                            })
                            ->thenInvalid('You must specify callback OR generator OR service but not collection of them.')
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
                            ->arrayNode('widget_generator')
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
