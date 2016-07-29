<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\View\Widget\WidgetPool;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Actions view widget
 */
class ActionsWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    private $widgetPool;

    /**
     * @param \Darvin\AdminBundle\View\Widget\WidgetPool $widgetPool View widget pool
     */
    public function setWidgetPool(WidgetPool $widgetPool)
    {
        $this->widgetPool = $widgetPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        $actions = [];

        $configuration = $this->metadataManager->getConfiguration($entity);

        foreach ($configuration['view'][$options['view_type']]['action_widgets'] as $widgetAlias => $widgetOptions) {
            $action = $this->widgetPool->getWidget($widgetAlias)->getContent($entity, $widgetOptions);

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
