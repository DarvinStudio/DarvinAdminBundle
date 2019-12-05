<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index\Body;

/**
 * Index view body row
 */
class BodyRow
{
    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\Body\BodyRowItem[]
     */
    private $items;

    /**
     * @var array
     */
    private $attr;

    /**
     * @param array $attr Attributes
     */
    public function __construct(array $attr = [])
    {
        $this->items = [];
        $this->attr = $attr;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return count($this->items);
    }

    /**
     * @param string|null                                             $field Field name
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\BodyRowItem $item  Row item
     */
    public function addItem(?string $field, BodyRowItem $item): void
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
     * @return \Darvin\AdminBundle\View\Factory\Index\Body\BodyRowItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return $this->attr;
    }
}
