<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
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
        $keysProperty = !empty($options['keys_property']) ? $options['keys_property'] : $options['property'];

        $keys = $this->getPropertyValue($entity, $keysProperty);

        if (null === $keys) {
            return null;
        }
        if (!is_iterable($keys)) {
            $message = sprintf(
                'Value of keys property "%s::$%s" must be iterable, "%s" provided.',
                get_class($entity),
                $keysProperty,
                gettype($keys)
            );

            throw new \InvalidArgumentException($message);
        }

        $values = $options['values_callback']();

        if (!is_iterable($values)) {
            throw new \InvalidArgumentException(
                sprintf('Values callback must return iterable, "%s" provided.', gettype($values))
            );
        }

        $list = [];

        foreach ($keys as $key) {
            foreach ($values as $valueKey => $value) {
                if ($valueKey === $key) {
                    $list[$key] = $value;
                }
            }
        }
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
            ->setDefaults([
                'keys_property' => null,
                'sort'          => true,
            ])
            ->setRequired('values_callback')
            ->setAllowedTypes('keys_property', ['string', 'null'])
            ->setAllowedTypes('sort', 'boolean')
            ->setAllowedTypes('values_callback', 'callable');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
