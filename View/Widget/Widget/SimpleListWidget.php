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

/**
 * Simple list view widget
 */
class SimpleListWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $property = $options['property'];

        $items = $this->getPropertyValue($entity, $property);

        if (empty($items)) {
            return null;
        }
        if (!is_iterable($items)) {
            $message = sprintf(
                'Value of property "%s::$%s" must be iterable, "%s" provided.',
                get_class($entity),
                $property,
                gettype($items)
            );

            throw new \InvalidArgumentException($message);
        }

        return $this->render([
            'items' => $items,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
