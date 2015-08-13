<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 11:18
 */

namespace Darvin\AdminBundle\View\Index\Body;

/**
 * Index view body row item
 */
class BodyRowItem
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content Content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
