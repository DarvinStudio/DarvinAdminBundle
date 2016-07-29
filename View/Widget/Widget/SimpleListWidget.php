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
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple list view widget
 */
class SimpleListWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        if (isset($options['property'])) {
            $property = $options['property'];
        }

        $items = $this->getPropertyValue($entity, $property);

        if (!is_array($items) && !$items instanceof \Traversable) {
            $message = sprintf(
                'Property "%s::$%s" must contain array or instance of \Traversable, "%s" provided.',
                ClassUtils::getClass($entity),
                $property,
                gettype($items)
            );

            throw new WidgetException($message);
        }

        return $this->render($options, [
            'items' => $items,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefined('property')
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
