<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Index\Body;

/**
 * Index view body row
 */
class BodyRow
{
    /**
     * @var \Darvin\AdminBundle\View\Index\Body\BodyRowItem[]
     */
    private $items;

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRowItem[] $items Row items
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    /**
     * @param string                                          $field Field name
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRowItem $item  Row item
     *
     * @return BodyRow
     */
    public function addItem($field, BodyRowItem $item)
    {
        $this->items[$field] = $item;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Body\BodyRowItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
