<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
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
    public function __construct(ViewWidgetPoolInterface $widgetPool)
    {
        $this->widgetPool = $widgetPool;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $collection = $this->getPropertyValue($entity, $options['property']);

        if (empty($collection)) {
            return null;
        }
        if (!is_iterable($collection)) {
            throw new \InvalidArgumentException(sprintf('Entity collection must be iterable, "%s" provided.', gettype($collection)));
        }
        if ($options['first_item_only']) {
            $items = [];

            foreach ($collection as $item) {
                $items[] = $item;

                break;
            }
            if (empty($items)) {
                return null;
            }

            $collection = $items;
        }

        $widgets = [];
        $render  = function (array $widgets): ?string {
            if (empty($widgets)) {
                return null;
            }

            return sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $widgets));
        };

        if (null === $options['item_widget_alias']) {
            foreach ($collection as $item) {
                $widgets[] = null !== $options['item_title_property'] ? $this->getPropertyValue($item, $options['item_title_property']) : $item;
            }

            return $render($widgets);
        }

        $widgetObject = $this->widgetPool->getWidget($options['item_widget_alias']);

        foreach ($collection as $item) {
            $widgets[] = $widgetObject->getContent($item, $options['item_widget_options']);
        }

        return $render($widgets);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'first_item_only'     => false,
                'item_title_property' => null,
                'item_widget_alias'   => ShowLinkWidget::ALIAS,
                'item_widget_options' => [
                    'text' => true,
                ],
            ])
            ->setAllowedTypes('first_item_only', 'boolean')
            ->setAllowedTypes('item_title_property', ['string', 'null'])
            ->setAllowedTypes('item_widget_alias', ['string', 'null'])
            ->setAllowedTypes('item_widget_options', 'array');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
