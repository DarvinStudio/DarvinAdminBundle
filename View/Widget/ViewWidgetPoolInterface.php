<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget;

/**
 * View widget pool
 */
interface ViewWidgetPoolInterface
{
    /**
     * @param string $alias View widget alias
     *
     * @return \Darvin\AdminBundle\View\Widget\WidgetInterface
     */
    public function getWidget(string $alias): WidgetInterface;

    /**
     * @return string[]
     */
    public function getWidgetAliases(): array;

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetInterface[]
     */
    public function getWidgets(): array;
}
