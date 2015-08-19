<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
