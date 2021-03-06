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
use Symfony\Component\Form\FormInterface;

/**
 * CRUD deleted event
 */
class DeletedEvent extends AbstractFormEvent
{
    /**
     * @var object
     */
    private $entity;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     * @param \Darvin\UserBundle\Entity\BaseUser    $user     User
     * @param \Symfony\Component\Form\FormInterface $form     Form
     * @param object                                $entity   Entity
     */
    public function __construct(Metadata $metadata, BaseUser $user, FormInterface $form, object $entity)
    {
        parent::__construct($metadata, $user, $form);

        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }
}
