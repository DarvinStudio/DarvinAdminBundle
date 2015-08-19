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
 * Index view body
 */
class Body
{
    /**
     * @var \Darvin\AdminBundle\View\Index\Body\BodyRow[]
     */
    private $rows;

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRow[] $rows Rows
     */
    public function __construct(array $rows = array())
    {
        $this->rows = $rows;
    }

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\BodyRow $row Row
     *
     * @return Body
     */
    public function addRow(BodyRow $row)
    {
        $this->rows[] = $row;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Body\BodyRow[]
     */
    public function getRows()
    {
        return $this->rows;
    }
}
