<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\AdminBundle\Form\Type\BatchDeleteType;
use Darvin\AdminBundle\Form\Type\FilterType;
use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouter;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Admin form factory
 */
class AdminFormFactory
{
    const SUBMIT_EDIT  = 'submit_edit';
    const SUBMIT_INDEX = 'submit_index';
    const SUBMIT_NEW   = 'submit_new';

    /**
     * @var array
     */
    private static $submitButtons = [
        self::SUBMIT_EDIT  => 'submit.edit',
        self::SUBMIT_INDEX => 'submit.index',
        self::SUBMIT_NEW   => 'submit.new',
    ];

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouter
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $identifierAccessor;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter                       $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface                $genericFormFactory Generic form factory
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor             $identifierAccessor Identifier accessor
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     */
    public function __construct(
        AdminRouter $adminRouter,
        FormFactoryInterface $genericFormFactory,
        IdentifierAccessor $identifierAccessor,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericFormFactory = $genericFormFactory;
        $this->identifierAccessor = $identifierAccessor;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param string   $entityClass Entity class
     * @param object[] $entities    Entities
     *
     * @return \Symfony\Component\Form\FormInterface
     * @throws \Darvin\AdminBundle\Form\FormException
     */
    public function createBatchDeleteForm($entityClass, array $entities = null)
    {
        if (null !== $entities && empty($entities)) {
            throw new FormException(
                sprintf('Unable to create batch delete form for entity class "%s": entity array is empty.', $entityClass)
            );
        }

        $options = [
            'entity_class' => $entityClass,
        ];

        if (!empty($entities)) {
            $options = array_merge($options, [
                'action'   => $this->adminRouter->generate(reset($entities), $entityClass, AdminRouter::TYPE_BATCH_DELETE),
                'entities' => $entities,
            ]);
        }

        return $this->genericFormFactory->create(BatchDeleteType::class, null, $options);
    }

    /**
     * @param object $entity      Entity
     * @param string $entityClass Entity class
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCopyForm($entity, $entityClass)
    {
        return $this->createIdForm($entity, 'copy_', $this->adminRouter->generate($entity, $entityClass, AdminRouter::TYPE_COPY));
    }

    /**
     * @param object $entity      Entity
     * @param string $entityClass Entity class
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDeleteForm($entity, $entityClass)
    {
        return $this->createIdForm($entity, 'delete_', $this->adminRouter->generate($entity, $entityClass, AdminRouter::TYPE_DELETE));
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta          Metadata
     * @param object                                $entity        Entity
     * @param string                                $actionType    Action type
     * @param string                                $formAction    Form action
     * @param array                                 $submitButtons Submit button names
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEntityForm(Metadata $meta, $entity, $actionType, $formAction, array $submitButtons)
    {
        $options = [
            'action'             => $formAction,
            'action_type'        => $actionType,
            'data_class'         => $meta->getEntityClass(),
            'metadata'           => $meta,
            'translation_domain' => 'admin',
            'validation_groups'  => [
                'Default',
                'Admin'.ucfirst($actionType),
            ],
        ];

        $configuration = $meta->getConfiguration();

        $type = !empty($configuration['form'][$actionType]['type']) ? $configuration['form'][$actionType]['type'] : BaseType::class;

        $builder = $this->genericFormFactory->createNamedBuilder($meta->getFormTypeName(), $type, $entity, $options);

        $buttonCount = count($submitButtons);

        foreach ($submitButtons as $name) {
            $builder->add($name, SubmitType::class, [
                'label' => 1 === $buttonCount ? 'submit.common' : static::$submitButtons[$name],
            ]);
        }

        return $builder->getForm();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta                         Metadata
     * @param string                                $parentEntityAssociationParam Parent entity association query parameter name
     * @param mixed                                 $parentEntityId               Parent entity ID
     * @param array                                 $options                      Options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFilterForm(Metadata $meta, $parentEntityAssociationParam = null, $parentEntityId = null, array $options = [])
    {
        if (!$meta->isFilterFormEnabled() || !$this->adminRouter->exists($meta->getEntityClass(), AdminRouter::TYPE_INDEX)) {
            return null;
        }
        if (!array_key_exists('action', $options)) {
            $actionRouteParams = [
                // Do not allow preserve filter data in URL event listener to work
                $meta->getFilterFormTypeName() => [],
            ];

            if (!empty($parentEntityAssociationParam)) {
                $actionRouteParams[$parentEntityAssociationParam] = $parentEntityId;
            }

            $options['action'] = $this->adminRouter->generate(null, $meta->getEntityClass(), AdminRouter::TYPE_INDEX, $actionRouteParams);
        }

        $configuration = $meta->getConfiguration();
        $type = $configuration['form']['filter']['type'];

        if (empty($type)) {
            $type = FilterType::class;

            $options = array_merge($options, [
                'metadata'                        => $meta,
                'parent_entity_association_param' => $parentEntityAssociationParam,
                'parent_entity_id'                => $parentEntityId,
            ]);
        }

        return $this->genericFormFactory->createNamed($meta->getFilterFormTypeName(), $type, null, $options);
    }

    /**
     * @param object $entity     Entity
     * @param string $namePrefix Form name prefix
     * @param string $action     Form action
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createIdForm($entity, $namePrefix, $action)
    {
        $id = $this->identifierAccessor->getValue($entity);

        $builder = $this->genericFormFactory->createNamedBuilder(
            $namePrefix.$id,
            FormType::class,
            [
                'id' => $id,
            ],
            [
                'action'             => $action,
                'csrf_token_id'      => md5(__FILE__.ClassUtils::getClass($entity).$id),
                'translation_domain' => 'admin',
            ]
        )->add('id', HiddenType::class);

        return $builder->getForm();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param string                                $property Property
     * @param object                                $entity   Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createPropertyForm(Metadata $meta, $property, $entity)
    {
        $dataClass = $meta->getEntityClass();

        if (!empty($entity) && !$this->propertyAccessor->isWritable($entity, $property) && null !== $meta->getTranslationClass()) {
            /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translatable $entity */
            $translations = $entity->getTranslations();

            /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translation $translation */
            foreach ($translations as $translation) {
                if ($translation->getLocale() === $entity->getCurrentLocale()) {
                    $dataClass = $meta->getTranslationClass();
                    $entity = $translation;
                }
            }
        }

        return $this->genericFormFactory->createNamed($meta->getFormTypeName().'_property', BaseType::class, $entity, [
            'action_type'       => 'index',
            'data_class'        => $dataClass,
            'field_filter'      => $property,
            'metadata'          => $meta,
            'required'          => false,
            'validation_groups' => [
                'Default',
                'AdminUpdateProperty',
            ],
        ]);
    }
}
