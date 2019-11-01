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
class DateTimeWidget extends AbstractWidget
{
    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $datetime = $this->getPropertyValue($entity, $options['property']);

        if (!$datetime instanceof \DateTime) {
            return null;
        }

        return $this->render([
            'date' => $datetime->format('d.m.y'),
            'time' => $datetime->format('H:i'),
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
