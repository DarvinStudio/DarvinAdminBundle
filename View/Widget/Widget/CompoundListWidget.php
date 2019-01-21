<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\WidgetException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Compound list view widget
 */
class CompoundListWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $keys = $this->getPropertyValue($entity, isset($options['keys_property']) ? $options['keys_property'] : $options['property']);

        if (null === $keys) {
            return null;
        }
        if (!is_array($keys) && !$keys instanceof \Traversable) {
            $message = sprintf(
                'Keys property "%s::$%s" must contain array or instance of \Traversable, "%s" provided.',
                get_class($entity),
                $options['keys_property'],
                gettype($keys)
            );

            throw new WidgetException($message);
        }

        $list = $this->createList($keys, $this->getValues($options));

        if (empty($list)) {
            return null;
        }
        if ($options['sort']) {
            sort($list);
        }

        return $this->render([
            'list' => $list,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('sort', true)
            ->setRequired('values_callback')
            ->setDefined('keys_property')
            ->setAllowedTypes('keys_property', 'string')
            ->setAllowedTypes('values_callback', 'callable')
            ->setAllowedTypes('sort', 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }

    /**
     * @param array $keys   Keys
     * @param array $values Values
     *
     * @return array
     */
    private function createList(array $keys, array $values): array
    {
        $list = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $values)) {
                $list[$key] = $values[$key];
            }
        }

        return $list;
    }

    /**
     * @param array $options Options
     *
     * @return array
     * @throws \Darvin\AdminBundle\View\Widget\WidgetException
     */
    private function getValues(array $options): array
    {
        $valuesCallback = $options['values_callback'];
        $values = $valuesCallback();

        if (!is_array($values) && !$values instanceof \Traversable) {
            throw new WidgetException(
                sprintf('Values callback must return array or instance of \Traversable, "%s" provided.', gettype($values))
            );
        }

        return $values;
    }
}
