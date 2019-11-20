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
     * @var Item[]
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
        $this->active = false;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        if (null === $this->indexTitle) {
            return true;
        }

        return null === $this->indexUrl && !$this->hasChildren();
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return null !== $this->parentName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getIndexTitle(): ?string
    {
        return $this->indexTitle;
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
    public function getIndexUrl(): ?string
    {
        return $this->indexUrl;
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
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
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
     * @return string|null
     */
    public function getAssociatedObject(): ?string
    {
        return $this->associatedObject;
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
    public function getParentName(): ?string
    {
        return $this->parentName;
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
     * @return Item[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param Item[] $children children
     *
     * @return Item
     */
    public function setChildren(array $children): Item
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param Item $child child
     *
     * @return Item
     */
    public function addChild(Item $child): Item
    {
        $this->children[] = $child;

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
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
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
}
