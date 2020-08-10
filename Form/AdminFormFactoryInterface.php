<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Metadata\Metadata;
use Symfony\Component\Form\FormInterface;

/**
 * Admin form factory
 */
interface AdminFormFactoryInterface
{
    public const NAME_PREFIX_DELETE = 'delete_';

    public const SUBMIT_EDIT  = 'submit_edit';
    public const SUBMIT_INDEX = 'submit_index';
    public const SUBMIT_NEW   = 'submit_new';

    /**
     * @param string        $entityClass Entity class
     * @param object[]|null $entities    Entities
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createBatchDeleteForm(string $entityClass, ?array $entities = null): FormInterface;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta          Metadata
     * @param object                                $entity        Entity
     * @param string                                $actionType    Action type
     * @param string                                $formAction    Form action
     * @param array                                 $submitButtons Submit button names
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEntityForm(Metadata $meta, object $entity, string $actionType, string $formAction, array $submitButtons): FormInterface;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta                         Metadata
     * @param string|null                           $parentEntityAssociationParam Parent entity association query parameter name
     * @param mixed|null                            $parentEntityId               Parent entity ID
     * @param array                                 $options                      Options
     *
     * @return \Symfony\Component\Form\FormInterface|null
     */
    public function createFilterForm(Metadata $meta, ?string $parentEntityAssociationParam = null, $parentEntityId = null, array $options = []): ?FormInterface;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param string                                $property Property
     * @param object                                $entity   Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createPropertyForm(Metadata $meta, string $property, object $entity): FormInterface;

    /**
     * @param object      $entity      Entity
     * @param string|null $entityClass Entity class
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCopyForm(object $entity, ?string $entityClass = null): FormInterface;

    /**
     * @param object      $entity      Entity
     * @param string|null $entityClass Entity class
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDeleteForm(object $entity, ?string $entityClass = null): FormInterface;
}
