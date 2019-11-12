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
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $datetime = $this->getPropertyValue($entity, $options['property']);

        if (!$datetime instanceof \DateTime) {
            $message = sprintf(
                'View widget "%s" requires entity to be instance of DateTime class.',
                $this->getAlias()
            );

            throw new \InvalidArgumentException($message);
        }

        return $this->render([
            'date' => $datetime->format('d.m.Y'),
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
