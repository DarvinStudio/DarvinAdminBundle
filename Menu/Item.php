<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * @var string|null
     */
    protected $indexTitle;

    /**
     * @var string|null
     */
    protected $indexUrl;

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
     * @var int|null
     */
    protected $newObjectCount;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @param string $name Name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $this->children = [];
        $this->active   = false;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return !empty($this->parentName);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return null === $this->indexUrl && empty($this->children);
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
     * @param int|null $newObjectCount newObjectCount
     *
     * @return Item
     */
    public function setNewObjectCount(?int $newObjectCount): Item
    {
        $this->newObjectCount = $newObjectCount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNewObjectCount(): ?int
    {
        return $this->newObjectCount;
    }

    /**
     * @param bool $active active
     *
     * @return Item
     */
    public function setActive(bool $active): Item
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
