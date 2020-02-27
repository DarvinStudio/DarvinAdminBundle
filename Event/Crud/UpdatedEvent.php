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
use Symfony\Component\Form\FormInterface;

/**
 * CRUD updated event
 */
class UpdatedEvent extends AbstractEvent
{
    /**
     * @var object
     */
    private $entityBefore;

    /**
     * @var object
     */
    private $entityAfter;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata     Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user         User
     * @param \Symfony\Component\Form\FormInterface $form         Form
     * @param object                                $entityBefore Entity before
     * @param object                                $entityAfter  Entity after
     */
    public function __construct(Metadata $metadata, BaseUser $user, FormInterface $form, object $entityBefore, object $entityAfter)
    {
        parent::__construct($metadata, $user, $form);

        $this->entityBefore = $entityBefore;
        $this->entityAfter = $entityAfter;
    }

    /**
     * @return object
     */
    public function getEntityBefore(): object
    {
        return $this->entityBefore;
    }

    /**
     * @return object
     */
    public function getEntityAfter(): object
    {
        return $this->entityAfter;
    }
}
