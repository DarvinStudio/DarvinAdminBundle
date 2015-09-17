<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @var string
     */
    private $sortablePropertyPath;

    /**
     * @var int
     */
    private $width;

    /**
     * @param string $content              Content
     * @param bool   $sortable             Is sortable
     * @param string $sortablePropertyPath Sortable property path
     * @param int    $width                Width
     */
    public function __construct($content, $sortable = false, $sortablePropertyPath = null, $width = 1)
    {
        $this->content = $content;
        $this->sortable = $sortable;
        $this->setSortablePropertyPath($sortablePropertyPath);
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
     * @param boolean $sortable sortable
     *
     * @return HeadItem
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @param string $sortablePropertyPath sortablePropertyPath
     *
     * @return HeadItem
     */
    public function setSortablePropertyPath($sortablePropertyPath)
    {
        if (!empty($sortablePropertyPath) && false === strpos($sortablePropertyPath, '.')) {
            $sortablePropertyPath = 'o.'.$sortablePropertyPath;
        }

        $this->sortablePropertyPath = $sortablePropertyPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortablePropertyPath()
    {
        return $this->sortablePropertyPath;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}
