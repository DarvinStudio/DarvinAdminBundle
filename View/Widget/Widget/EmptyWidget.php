<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
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
     * {@inheritdoc}
     */
    public function getContent($entity, array $options = [], $property = null)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'empty_widget';
    }
}
