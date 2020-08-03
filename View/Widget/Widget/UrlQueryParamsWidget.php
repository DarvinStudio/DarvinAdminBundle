<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * URL query params view widget
 */
class UrlQueryParamsWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
     */
    protected function createContent(object $entity, array $options): ?string
    {
        $url = (string)$this->getPropertyValue($entity, $options['property']);

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
