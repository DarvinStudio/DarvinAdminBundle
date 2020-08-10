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

/**
 * Phone link view widget
 */
class PhoneLinkWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $phone = (string)$this->getPropertyValue($entity, $options['property']);
        $url   = preg_replace('/[^\+\d]+/', '', $phone);

        if ('' !== $url) {
            return sprintf('<a href="tel:%s">%s</a>', $url, $phone);
        }

        return $phone;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
