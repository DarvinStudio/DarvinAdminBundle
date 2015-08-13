<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 11:25
 */

namespace Darvin\AdminBundle\View\Index\Head;

/**
 * Index view head item
 */
class HeadItem
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var bool
     */
    private $sortable;

    /**
     * @var int
     */
    private $width;

    /**
     * @param string $content  Content
     * @param bool   $sortable Is sortable
     * @param int    $width    Width
     */
    public function __construct($content, $sortable = false, $width = 1)
    {
        $this->content = $content;
        $this->sortable = $sortable;
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return boolean
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}
