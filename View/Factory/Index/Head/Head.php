<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index\Head;

/**
 * Index view head
 */
class Head
{
    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\Head\HeadItem[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Head\HeadItem[] $items Items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param string|null                                          $field Field name
     * @param \Darvin\AdminBundle\View\Factory\Index\Head\HeadItem $item  Item
     */
    public function addItem(?string $field, HeadItem $item): void
    {
        $this->items[$field] = $item;
    }

    /**
     * @param string|null $field Field name
     */
    public function removeItem(?string $field): void
    {
        unset($this->items[$field]);
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Index\Head\HeadItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
