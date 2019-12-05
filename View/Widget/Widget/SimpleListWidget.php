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
use Doctrine\Common\Util\ClassUtils;

/**
 * Simple list view widget
 */
class SimpleListWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
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
                ClassUtils::getClass($entity),
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
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
