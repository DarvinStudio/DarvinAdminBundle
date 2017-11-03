<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Crud;

use Darvin\UserBundle\Entity\BaseUser;
use Symfony\Component\EventDispatcher\Event;

/**
 * CRUD event abstract implementation
 */
abstract class AbstractEvent extends Event
{
    /**
     * @var \Darvin\UserBundle\Entity\BaseUser
     */
    private $user;

    /**
     * @param \Darvin\UserBundle\Entity\BaseUser $user User
     */
    public function __construct(BaseUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return \Darvin\UserBundle\Entity\BaseUser
     */
    public function getUser()
    {
        return $this->user;
    }
}
