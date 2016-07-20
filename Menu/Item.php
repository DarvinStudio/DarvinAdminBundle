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
class Item
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $indexTitle;

    /**
     * @var string
     */
    protected $newTitle;

    /**
     * @var string
     */
    protected $indexUrl;

    /**
     * @var string
     */
    protected $newUrl;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $mainColor;

    /**
     * @var string
     */
    protected $sidebarColor;

    /**
     * @var string
     */
    protected $mainIcon;

    /**
     * @var string
     */
    protected $sidebarIcon;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $associatedObject;

    /**
     * @var string
     */
    protected $parentName;

    /**
     * @var \Darvin\AdminBundle\Menu\Item[]
     */
    protected $children;

    /**
     * @param string $name Name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->indexUrl = $this->newUrl = '#';
        $this->children = [];
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return !empty($this->parentName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $indexTitle indexTitle
     *
     * @return Item
     */
    public function setIndexTitle($indexTitle)
    {
        $this->indexTitle = $indexTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexTitle()
    {
        return $this->indexTitle;
    }

    /**
     * @param string $newTitle newTitle
     *
     * @return Item
     */
    public function setNewTitle($newTitle)
    {
        $this->newTitle = $newTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewTitle()
    {
        return $this->newTitle;
    }

    /**
     * @param string $indexUrl indexUrl
     *
     * @return Item
     */
    public function setIndexUrl($indexUrl)
    {
        $this->indexUrl = $indexUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexUrl()
    {
        return $this->indexUrl;
    }

    /**
     * @param string $newUrl newUrl
     *
     * @return Item
     */
    public function setNewUrl($newUrl)
    {
        $this->newUrl = $newUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewUrl()
    {
        return $this->newUrl;
    }

    /**
     * @param string $description description
     *
     * @return Item
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $mainColor mainColor
     *
     * @return Item
     */
    public function setMainColor($mainColor)
    {
        $this->mainColor = $mainColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getMainColor()
    {
        return $this->mainColor;
    }

    /**
     * @param string $sidebarColor sidebarColor
     *
     * @return Item
     */
    public function setSidebarColor($sidebarColor)
    {
        $this->sidebarColor = $sidebarColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarColor()
    {
        return $this->sidebarColor;
    }

    /**
     * @param string $mainIcon mainIcon
     *
     * @return Item
     */
    public function setMainIcon($mainIcon)
    {
        $this->mainIcon = $mainIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getMainIcon()
    {
        return $this->mainIcon;
    }

    /**
     * @param string $sidebarIcon sidebarIcon
     *
     * @return Item
     */
    public function setSidebarIcon($sidebarIcon)
    {
        $this->sidebarIcon = $sidebarIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarIcon()
    {
        return $this->sidebarIcon;
    }

    /**
     * @param int $position position
     *
     * @return Item
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $associatedObject associatedObject
     *
     * @return Item
     */
    public function setAssociatedObject($associatedObject)
    {
        $this->associatedObject = $associatedObject;

        return $this;
    }

    /**
     * @return string
     */
    public function getAssociatedObject()
    {
        return $this->associatedObject;
    }

    /**
     * @param string $parentName parentName
     *
     * @return Item
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;

        return $this;
    }

    /**
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @param Item $child Child menu item
     *
     * @return Item
     */
    public function addChild(Item $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    public function getChildren()
    {
        return $this->children;
    }
}
