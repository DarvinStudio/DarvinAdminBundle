<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Factory\Index;

use Darvin\AdminBundle\View\Factory\Index\Body\Body;
use Darvin\AdminBundle\View\Factory\Index\Head\Head;

/**
 * Index view
 */
class IndexView
{
    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\Head\Head
     */
    private $head;

    /**
     * @var \Darvin\AdminBundle\View\Factory\Index\Body\Body
     */
    private $body;

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Head\Head $head head
     */
    public function setHead(Head $head): void
    {
        $this->head = $head;
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Index\Head\Head
     */
    public function getHead(): Head
    {
        return $this->head;
    }

    /**
     * @param \Darvin\AdminBundle\View\Factory\Index\Body\Body $body body
     */
    public function setBody(Body $body): void
    {
        $this->body = $body;
    }

    /**
     * @return \Darvin\AdminBundle\View\Factory\Index\Body\Body
     */
    public function getBody(): Body
    {
        return $this->body;
    }
}
