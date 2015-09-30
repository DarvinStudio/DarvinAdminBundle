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
 * Menu item group
 */
class MenuItemGroup implements MenuItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private $items;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param string                                       $name       Group name
     * @param array                                        $attributes Group attributes
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface[] $items      Menu items
     */
    public function __construct($name, array $attributes = array(), array $items = array())
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->items = $items;
        $this->updateAttributes();
    }

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item Menu item
     */
    public function addItem(MenuItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildMenuItems(array $childMenuItems)
    {
        $this->items = $childMenuItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildMenuItems()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMenuItemAttributes(array $menuItemAttributes)
    {
        $this->attributes = $menuItemAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItemAttributes()
    {
        return $this->attributes;
    }

    private function updateAttributes()
    {
        $this->attributes = array_merge(array(
            'description' => sprintf('menu.group.%s.description', $this->name),
            'index_title' => sprintf('menu.group.%s.title', $this->name),
            'name'        => $this->name,
        ), $this->attributes);
    }
}
