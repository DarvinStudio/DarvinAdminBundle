<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\UserBundle\Entity\BaseUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\Event;

/**
 * CRUD controller index action event
 */
class CrudControllerIndexActionEvent extends Event
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $metadata;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $qb;

    /**
     * @var \Darvin\UserBundle\Entity\BaseUser
     */
    private $user;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param \Doctrine\ORM\QueryBuilder            $qb       Query builder
     * @param \Darvin\UserBundle\Entity\BaseUser    $user     User
     */
    public function __construct(Metadata $metadata, QueryBuilder $qb, BaseUser $user)
    {
        $this->metadata = $metadata;
        $this->qb = $qb;
        $this->user = $user;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQb()
    {
        return $this->qb;
    }

    /**
     * @return \Darvin\UserBundle\Entity\BaseUser
     */
    public function getUser()
    {
        return $this->user;
    }
}
