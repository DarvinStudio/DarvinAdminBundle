<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\WidgetException;
use Darvin\AdminBundle\View\Widget\WidgetPool;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Entity list view widget
 */
class EntityListWidget extends AbstractWidget
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
        $collection = $this->getPropertyValue($entity, isset($options['property']) ? $options['property'] : $property);

        if (empty($collection)) {
            return null;
        }
        if (!is_array($collection)) {
            if (!is_object($collection)) {
                throw new WidgetException(
                    sprintf('Entities collection must be array or object, "%s" provided.', gettype($collection))
                );
            }
            if (!$collection instanceof \Traversable) {
                throw new WidgetException(
                    sprintf('Entities collection object "%s" must be instance of \Traversable.', get_class($collection))
                );
            }
        }
        if (empty($options['item_widget_alias'])) {
            $widgets = [];

            if (!isset($options['item_title_property'])) {
                foreach ($collection as $item) {
                    $widgets[] = $item;

                    if ($options['first_item_only']) {
                        break;
                    }
                }

                return $this->render($options, [
                    'widgets' => $widgets,
                ]);
            }
            foreach ($collection as $item) {
                $widgets[] = $this->getPropertyValue($item, $options['item_title_property']);

                if ($options['first_item_only']) {
                    break;
                }
            }

            return $this->render($options, [
                'widgets' => $widgets,
            ]);
        }

        $widget = $this->widgetPool->getWidget($options['item_widget_alias']);

        $widgets = [];

        foreach ($collection as $item) {
            $widgets[] = $widget->getContent($item, $options['item_widget_options']);

            if ($options['first_item_only']) {
                break;
            }
        }

        return $this->render($options, [
            'widgets' => $widgets,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'first_item_only'     => false,
                'item_widget_alias'   => ShowLinkWidget::ALIAS,
                'item_widget_options' => [
                    'text_link' => true,
                ],
            ])
            ->setDefined([
                'item_title_property',
                'property',
            ])
            ->setAllowedTypes('item_title_property', 'string')
            ->setAllowedTypes('first_item_only', 'boolean')
            ->setAllowedTypes('item_widget_alias', [
                'null',
                'string',
            ])
            ->setAllowedTypes('item_widget_options', 'array')
            ->setAllowedTypes('property', 'string');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions()
    {
        return [
            Permission::VIEW,
        ];
    }
}
