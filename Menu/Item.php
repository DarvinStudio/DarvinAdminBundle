<?php
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
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $indexTitle;

    /**
     * @var string
     */
    private $newTitle;

    /**
     * @var string
     */
    private $indexUrl;

    /**
     * @var string
     */
    private $newUrl;

    /**
     * @var int
     */
    private $position;

    /**
     * @param string $name       Name
     * @param string $indexTitle Index action title
     */
    public function __construct($name, $indexTitle)
    {
        $this->name = $name;
        $this->indexTitle = $indexTitle;

        $this->indexUrl = '#';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIndexTitle()
    {
        return $this->indexTitle;
    }

    /**
     * @param string $newTitle newTitle
     *
     * @return Item
     */
    public function setNewTitle($newTitle)
    {
        $this->newTitle = $newTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewTitle()
    {
        return $this->newTitle;
    }

    /**
     * @param string $indexUrl indexUrl
     *
     * @return Item
     */
    public function setIndexUrl($indexUrl)
    {
        $this->indexUrl = $indexUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexUrl()
    {
        return $this->indexUrl;
    }

    /**
     * @param string $newUrl newUrl
     *
     * @return Item
     */
    public function setNewUrl($newUrl)
    {
        $this->newUrl = $newUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewUrl()
    {
        return $this->newUrl;
    }

    /**
     * @param int $position position
     *
     * @return Item
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
