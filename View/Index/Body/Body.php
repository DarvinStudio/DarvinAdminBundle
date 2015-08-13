<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 11:16
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
