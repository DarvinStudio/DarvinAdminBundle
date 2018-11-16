<?php declare(strict_types=1);
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
    const DEFAULT_MAIN_ICON    = 'bundles/darvinadmin/images/main_menu_stub.png';
    const DEFAULT_SIDEBAR_ICON = 'bundles/darvinadmin/images/sidebar_menu_stub.png';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $indexTitle;

    /**
     * @var string|null
     */
    protected $newTitle;

    /**
     * @var string|null
     */
    protected $indexUrl;

    /**
     * @var string|null
     */
    protected $newUrl;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $mainColor;

    /**
     * @var string|null
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
     * @var int|null
     */
    protected $position;

    /**
     * @var string|null
     */
    protected $associatedObject;

    /**
     * @var string|null
     */
    protected $parentName;

    /**
     * @var \Darvin\AdminBundle\Menu\Item[]
     */
    protected $children;

    /**
     * @var int
     */
    protected $newObjectCount;

    /**
     * @param string $name Name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $this->mainIcon = self::DEFAULT_MAIN_ICON;
        $this->sidebarIcon = self::DEFAULT_SIDEBAR_ICON;
        $this->children = [];
        $this->newObjectCount = 0;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return !empty($this->parentName);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $indexTitle indexTitle
     *
     * @return Item
     */
    public function setIndexTitle(?string $indexTitle): Item
    {
        $this->indexTitle = $indexTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIndexTitle(): ?string
    {
        return $this->indexTitle;
    }

    /**
     * @param string|null $newTitle newTitle
     *
     * @return Item
     */
    public function setNewTitle(?string $newTitle): Item
    {
        $this->newTitle = $newTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewTitle(): ?string
    {
        return $this->newTitle;
    }

    /**
     * @param string|null $indexUrl indexUrl
     *
     * @return Item
     */
    public function setIndexUrl(?string $indexUrl): Item
    {
        $this->indexUrl = $indexUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIndexUrl(): ?string
    {
        return $this->indexUrl;
    }

    /**
     * @param string|null $newUrl newUrl
     *
     * @return Item
     */
    public function setNewUrl(?string $newUrl): Item
    {
        $this->newUrl = $newUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewUrl(): ?string
    {
        return $this->newUrl;
    }

    /**
     * @param string|null $description description
     *
     * @return Item
     */
    public function setDescription(?string $description): Item
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $mainColor mainColor
     *
     * @return Item
     */
    public function setMainColor(?string $mainColor): Item
    {
        $this->mainColor = $mainColor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMainColor(): ?string
    {
        return $this->mainColor;
    }

    /**
     * @param string|null $sidebarColor sidebarColor
     *
     * @return Item
     */
    public function setSidebarColor(?string $sidebarColor): Item
    {
        $this->sidebarColor = $sidebarColor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSidebarColor(): ?string
    {
        return $this->sidebarColor;
    }

    /**
     * @param string $mainIcon mainIcon
     *
     * @return Item
     */
    public function setMainIcon(string $mainIcon): Item
    {
        $this->mainIcon = $mainIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getMainIcon(): string
    {
        return $this->mainIcon;
    }

    /**
     * @param string $sidebarIcon sidebarIcon
     *
     * @return Item
     */
    public function setSidebarIcon(string $sidebarIcon): Item
    {
        $this->sidebarIcon = $sidebarIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarIcon(): string
    {
        return $this->sidebarIcon;
    }

    /**
     * @param int|null $position position
     *
     * @return Item
     */
    public function setPosition(?int $position): Item
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param string|null $associatedObject associatedObject
     *
     * @return Item
     */
    public function setAssociatedObject(?string $associatedObject): Item
    {
        $this->associatedObject = $associatedObject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAssociatedObject(): ?string
    {
        return $this->associatedObject;
    }

    /**
     * @param string|null $parentName parentName
     *
     * @return Item
     */
    public function setParentName(?string $parentName): Item
    {
        $this->parentName = $parentName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    /**
     * @param Item $child Child menu item
     *
     * @return Item
     */
    public function addChild(Item $child): Item
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param int $newObjectCount newObjectCount
     *
     * @return Item
     */
    public function setNewObjectCount(int $newObjectCount): Item
    {
        $this->newObjectCount = $newObjectCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewObjectCount(): int
    {
        return $this->newObjectCount;
    }
}
