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
     * @param MenuItemInterface[] $childMenuItems Child menu items
     */
    public function setChildMenuItems(array $childMenuItems);

    /**
     * @return MenuItemInterface[]
     */
    public function getChildMenuItems();

    /**
     * @return string
     */
    public function getIndexUrl();

    /**
     * @return string
     */
    public function getNewUrl();

    /**
     * @param array $menuItemAttributes Menu item attributes
     */
    public function setMenuItemAttributes(array $menuItemAttributes);

    /**
     * @return array
     */
    public function getMenuItemAttributes();
}
