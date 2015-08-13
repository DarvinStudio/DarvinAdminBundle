<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.08.15
 * Time: 10:05
 */

namespace Darvin\AdminBundle\View\Index;

use Darvin\AdminBundle\View\Index\Body\Body;
use Darvin\AdminBundle\View\Index\Head\Head;

/**
 * Index view
 */
class IndexView
{
    /**
     * @var \Darvin\AdminBundle\View\Index\Head\Head
     */
    private $head;

    /**
     * @var \Darvin\AdminBundle\View\Index\Body\Body
     */
    private $body;

    /**
     * @param \Darvin\AdminBundle\View\Index\Head\Head $head head
     *
     * @return IndexView
     */
    public function setHead(Head $head)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Head\Head
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @param \Darvin\AdminBundle\View\Index\Body\Body $body body
     *
     * @return IndexView
     */
    public function setBody(Body $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\View\Index\Body\Body
     */
    public function getBody()
    {
        return $this->body;
    }
}
