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
 * Menu
 */
class Menu
{
    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private $items;

    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemGroup[]
     */
    private $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = array();
        $this->groups = array();
    }

    /**
     * @param string                                     $groupName Menu item group name
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item      Menu item
     */
    public function addItem($groupName, MenuItemInterface $item)
    {
        if (empty($groupName)) {
            $this->items[] = $item;

            return;
        }
        if (!isset($this->groups[$groupName])) {
            $group = new MenuItemGroup($groupName);
            $this->items[] = $group;
            $this->groups[$groupName] = $group;
        }

        $group = $this->groups[$groupName];
        $group->addItem($item);
    }

    /**
     * @return \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
