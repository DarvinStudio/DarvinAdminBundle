<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Form\Type\BatchDeleteType;
use Darvin\AdminBundle\Form\Type\EntityType;
use Darvin\AdminBundle\Form\Type\FilterType;
use Darvin\AdminBundle\Metadata\IdentifierAccessorInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Admin form factory
 */
class AdminFormFactory implements AdminFormFactoryInterface
{
    private const SUBMIT_BUTTONS = [
        AdminFormFactoryInterface::SUBMIT_EDIT  => 'submit.edit',
        AdminFormFactoryInterface::SUBMIT_INDEX => 'submit.index',
        AdminFormFactoryInterface::SUBMIT_NEW   => 'submit.new',
    ];

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    private $adminRouter;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $genericFormFactory;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface
     */
    private $identifierAccessor;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface              $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface                $genericFormFactory Generic form factory
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessorInterface    $identifierAccessor Identifier accessor
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor   Property accessor
     */
    public function __construct(
        AdminRouterInterface $adminRouter,
        FormFactoryInterface $genericFormFactory,
        IdentifierAccessorInterface $identifierAccessor,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericFormFactory = $genericFormFactory;
        $this->identifierAccessor = $identifierAccessor;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function createBatchDeleteForm(string $entityClass, ?array $entities = null): FormInterface
    {
        if (null !== $entities && empty($entities)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to create batch delete form for entity class "%s": entity array is empty.', $entityClass)
            );
        }

        $options = [];

        if (!empty($entities)) {
            $options['action'] = $this->adminRouter->generate(reset($entities), $entityClass, AdminRouterInterface::TYPE_BATCH_DELETE);
        }

        return $this->genericFormFactory->create(BatchDeleteType::class, null, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function createEntityForm(Metadata $meta, $entity, string $actionType, string $formAction, array $submitButtons): FormInterface
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

        $type = null !== $configuration['form'][$actionType]['type'] ? $configuration['form'][$actionType]['type'] : EntityType::class;

        $builder = $this->genericFormFactory->createNamedBuilder($meta->getFormTypeName(), $type, $entity, $options);

        $buttonCount = count($submitButtons);

        foreach ($submitButtons as $name) {
            $builder->add($name, SubmitType::class, [
                'label' => 1 === $buttonCount ? 'submit.common' : self::SUBMIT_BUTTONS[$name],
            ]);
        }

        return $builder->getForm();
    }

    /**
     * {@inheritDoc}
     */
    public function createFilterForm(Metadata $meta, ?string $parentEntityAssociationParam = null, $parentEntityId = null, array $options = []): ?FormInterface
    {
        if (!$meta->isFilterFormEnabled() || !$this->adminRouter->exists($meta->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            return null;
        }
        if (!array_key_exists('action', $options)) {
            $actionRouteParams = [];

            if (null !== $parentEntityAssociationParam) {
                $actionRouteParams[$parentEntityAssociationParam] = $parentEntityId;
            }

            $options['action'] = $this->adminRouter->generate(null, $meta->getEntityClass(), AdminRouterInterface::TYPE_INDEX, $actionRouteParams, UrlGeneratorInterface::ABSOLUTE_PATH, false);
        }

        $configuration = $meta->getConfiguration();

        $type = $configuration['form']['filter']['type'];

        if (null === $type) {
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
     * {@inheritDoc}
     */
    public function createPropertyForm(Metadata $meta, string $property, $entity): FormInterface
    {
        $dataClass = $meta->getEntityClass();

        if (null !== $entity && !$this->propertyAccessor->isWritable($entity, $property) && null !== $meta->getTranslationClass()) {
            /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translatable $entity */
            $translations = $entity->getTranslations();

            /** @var \Knp\DoctrineBehaviors\Model\Translatable\Translation $translation */
            foreach ($translations as $translation) {
                if ($translation->getLocale() === $entity->getCurrentLocale()) {
                    $dataClass = $meta->getTranslationClass();
                    $entity    = $translation;
                }
            }
        }

        $builder = $this->genericFormFactory->createNamedBuilder($meta->getFormTypeName().'_property', EntityType::class, $entity, [
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

        $builder->add('_', HiddenType::class, [
            'mapped' => false,
        ]);

        return $builder->getForm();
    }

    /**
     * {@inheritDoc}
     */
    public function createCopyForm($entity, ?string $entityClass = null): FormInterface
    {
        return $this->createIdForm($entity, 'copy_', $this->adminRouter->generate($entity, $entityClass, AdminRouterInterface::TYPE_COPY));
    }

    /**
     * {@inheritDoc}
     */
    public function createDeleteForm($entity, ?string $entityClass = null): FormInterface
    {
        return $this->createIdForm($entity, 'delete_', $this->adminRouter->generate($entity, $entityClass, AdminRouterInterface::TYPE_DELETE));
    }

    /**
     * @param object $entity     Entity
     * @param string $namePrefix Form name prefix
     * @param string $action     Form action
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createIdForm(object $entity, string $namePrefix, string $action): FormInterface
    {
        $id = $this->identifierAccessor->getId($entity);

        $builder = $this->genericFormFactory->createNamedBuilder(
            $namePrefix.$id,
            FormType::class,
            [
                'id' => $id,
            ],
            [
                'action'             => $action,
                'csrf_protection'    => false,
                'translation_domain' => 'admin',
            ]
        )->add('id', HiddenType::class);

        return $builder->getForm();
    }
}
