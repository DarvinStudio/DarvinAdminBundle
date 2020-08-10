<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;

/**
 * Date time view widget
 */
class DatetimeWidget extends AbstractWidget
{
    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $datetime = $this->getPropertyValue($entity, $options['property']);

        if (!$datetime instanceof \DateTime) {
            throw new \InvalidArgumentException(sprintf(
                'View widget "%s" requires property value to be instance of \DateTime class, got "%s".',
                $this->getAlias(),
                gettype($datetime)
            ));
        }

        return sprintf('%s<br><span>%s</span>', $datetime->format('d.m.Y'), $datetime->format('H:i'));
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
