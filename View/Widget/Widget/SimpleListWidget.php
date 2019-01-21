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
use Darvin\AdminBundle\View\Widget\WidgetException;

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

        if (!is_array($items) && !$items instanceof \Traversable) {
            $message = sprintf(
                'Property "%s::$%s" must contain array or instance of \Traversable, "%s" provided.',
                get_class($entity),
                $property,
                gettype($items)
            );

            throw new WidgetException($message);
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
