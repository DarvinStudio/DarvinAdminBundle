<?php declare(strict_types=1);
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
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Darvin\AdminBundle\View\Widget\WidgetException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Entity list view widget
 */
class EntityListWidget extends AbstractWidget
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private $widgetPool;

    /**
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface $widgetPool View widget pool
     */
    public function setWidgetPool(ViewWidgetPoolInterface $widgetPool)
    {
        $this->widgetPool = $widgetPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $collection = $this->getPropertyValue($entity, $options['property']);

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

                return $this->render([
                    'widgets' => $widgets,
                ]);
            }
            foreach ($collection as $item) {
                $widgets[] = $this->getPropertyValue($item, $options['item_title_property']);

                if ($options['first_item_only']) {
                    break;
                }
            }

            return $this->render([
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

        return $this->render([
            'widgets' => $widgets,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
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
            ->setDefined('item_title_property')
            ->setAllowedTypes('item_title_property', 'string')
            ->setAllowedTypes('first_item_only', 'boolean')
            ->setAllowedTypes('item_widget_alias', [
                'null',
                'string',
            ])
            ->setAllowedTypes('item_widget_options', 'array');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
