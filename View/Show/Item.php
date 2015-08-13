<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 12:32
 */

namespace Darvin\AdminBundle\View\Show;

/**
 * Show view item
 */
class Item
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $label   Label
     * @param string $content Content
     */
    public function __construct($label, $content)
    {
        $this->label = $label;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
