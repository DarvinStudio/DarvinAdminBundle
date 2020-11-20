<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Crud;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\UserBundle\Entity\BaseUser;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * CRUD event abstract implementation
 */
abstract class AbstractEvent extends Event
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    protected $metadata;

    /**
     * @var \Darvin\UserBundle\Entity\BaseUser
     */
    protected $user;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user     User
     */
    public function __construct(Metadata $metadata, BaseUser $user)
    {
        $this->metadata = $metadata;
        $this->user = $user;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @return \Darvin\UserBundle\Entity\BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }
}
