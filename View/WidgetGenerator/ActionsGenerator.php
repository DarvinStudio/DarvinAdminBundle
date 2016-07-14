<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Actions view widget generator
 */
class ActionsGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private $widgetGeneratorPool;

    /**
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool $widgetGeneratorPool View widget generator pool
     */
    public function setWidgetGeneratorPool(WidgetGeneratorPool $widgetGeneratorPool)
    {
        $this->widgetGeneratorPool = $widgetGeneratorPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, array $options, $property)
    {
        $actions = [];

        $configuration = $this->metadataManager->getConfiguration($entity);

        foreach ($configuration['view'][$options['view_type']]['action_widgets'] as $widgetGeneratorAlias => $widgetGeneratorOptions) {
            $action = $this->widgetGeneratorPool->getWidgetGenerator($widgetGeneratorAlias)->generate($entity, $widgetGeneratorOptions);

            if (!empty($action)) {
                $actions[] = $action;
            }
        }

        return $this->render($options, [
            'actions' => $actions,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('view_type')
            ->setAllowedTypes('view_type', 'string');
    }
}
