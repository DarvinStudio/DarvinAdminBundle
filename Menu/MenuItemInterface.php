<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

/**
 * Menu item
 */
interface MenuItemInterface
{
    /**
     * @param MenuItemInterface[] $children Child menu items
     */
    public function setChildren(array $children);

    /**
     * @return MenuItemInterface[]
     */
    public function getChildren();

    /**
     * @return string
     */
    public function getIndexUrl();

    /**
     * @return string
     */
    public function getNewUrl();

    /**
     * @return string
     */
    public function getIndexTitle();

    /**
     * @return string
     */
    public function getNewTitle();

    /**
     * @return string
     */
    public function getMenuName();

    /**
     * @return string
     */
    public function getMenuDescription();

    /**
     * @return string
     */
    public function getMenuColor();

    /**
     * @return string
     */
    public function getAssociatedObjectClass();
}
