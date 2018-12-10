<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Crud;

use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\UserBundle\Entity\BaseUser;

/**
 * CRUD copied event
 */
class CopiedEvent extends AbstractEvent
{
    /**
     * @var object
     */
    private $entityOriginal;

    /**
     * @var object
     */
    private $entityCopy;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata       Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user           User
     * @param object                                $entityOriginal Entity original
     * @param object                                $entityCopy     Entity copy
     */
    public function __construct(Metadata $metadata, BaseUser $user, $entityOriginal, $entityCopy)
    {
        parent::__construct($metadata, $user);

        $this->entityOriginal = $entityOriginal;
        $this->entityCopy = $entityCopy;
    }

    /**
     * @return object
     */
    public function getEntityOriginal()
    {
        return $this->entityOriginal;
    }

    /**
     * @return object
     */
    public function getEntityCopy()
    {
        return $this->entityCopy;
    }
}
