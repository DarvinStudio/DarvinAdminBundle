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

use Darvin\AdminBundle\View\Widget\WidgetInterface;

/**
 * Empty view widget
 */
class EmptyWidget implements WidgetInterface
{
    /**
     * {@inheritDoc}
     */
    public function getContent($entity, array $options = []): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return 'empty_widget';
    }
}
