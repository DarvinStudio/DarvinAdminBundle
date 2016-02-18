<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\AdminBundle\Form\Type\FilterType;
use Darvin\AdminBundle\Metadata\IdentifierAccessor;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouter;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormFactoryInterface;

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
    private static $submitButtons = array(
        self::SUBMIT_EDIT  => 'submit.edit',
        self::SUBMIT_INDEX => 'submit.index',
        self::SUBMIT_NEW   => 'submit.new',
    );

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
     * @param \Darvin\AdminBundle\Route\AdminRouter           $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface    $genericFormFactory Generic form factory
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor $identifierAccessor Identifier accessor
     */
    public function __construct(
        AdminRouter $adminRouter,
        FormFactoryInterface $genericFormFactory,
        IdentifierAccessor $identifierAccessor
    ) {
        $this->adminRouter = $adminRouter;
        $this->genericFormFactory = $genericFormFactory;
        $this->identifierAccessor = $identifierAccessor;
    }

    /**
     * @param object $entity Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCopyForm($entity)
    {
        return $this->createIdForm($entity, 'copy_', $this->adminRouter->generate($entity, null, AdminRouter::TYPE_COPY));
    }

    /**
     * @param object $entity Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDeleteForm($entity)
    {
        return $this->createIdForm($entity, 'delete_', $this->adminRouter->generate($entity, null, AdminRouter::TYPE_DELETE));
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
        $options = array(
            'action'             => $formAction,
            'data_class'         => $meta->getEntityClass(),
            'translation_domain' => 'admin',
            'validation_groups'  => array(
                'Default',
                'Admin'.ucfirst($actionType),
            ),
        );

        $configuration = $meta->getConfiguration();
        $type = $configuration['form'][$actionType]['type'];

        if (empty($type)) {
            $type = BaseType::BASE_TYPE_CLASS;

            $options = array_merge($options, array(
                'action_type' => $actionType,
                'metadata'    => $meta,
            ));
        }

        $builder = $this->genericFormFactory->createNamedBuilder($meta->getFormTypeName(), $type, $entity, $options);

        foreach ($submitButtons as $name) {
            $builder->add($name, 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array(
                'label' => self::$submitButtons[$name],
            ));
        }

        return $builder->getForm();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta                         Metadata
     * @param string                                $parentEntityAssociation      Parent entity association
     * @param string                                $parentEntityAssociationParam Parent entity association query parameter name
     * @param mixed                                 $parentEntityId               Parent entity ID
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFilterForm(
        Metadata $meta,
        $parentEntityAssociation = null,
        $parentEntityAssociationParam = null,
        $parentEntityId = null
    ) {
        if (!$meta->isFilterFormEnabled() || !$this->adminRouter->isRouteExists($meta->getEntityClass(), AdminRouter::TYPE_INDEX)) {
            return null;
        }

        $actionRouteParams = !empty($parentEntityAssociation)
            ? array(
                $parentEntityAssociationParam => $parentEntityId,
            )
            : array();
        $action = $this->adminRouter->generate(null, $meta->getEntityClass(), AdminRouter::TYPE_INDEX, $actionRouteParams);

        $options = array(
            'action' => $action,
        );

        $configuration = $meta->getConfiguration();
        $type = $configuration['form']['filter']['type'];

        if (empty($type)) {
            $type = FilterType::FILTER_TYPE_CLASS;

            $options = array_merge($options, array(
                'metadata'                  => $meta,
                'parent_entity_association' => $parentEntityAssociation,
                'parent_entity_id'          => $parentEntityId,
            ));
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
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            array(
                'id' => $id,
            ),
            array(
                'action'             => $action,
                'csrf_token_id'      => md5(__FILE__.ClassUtils::getClass($entity).$id),
                'translation_domain' => 'admin',
            )
        )->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType');

        return $builder->getForm();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta     Metadata
     * @param string                                $property Property
     * @param object                                $entity   Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createPropertyForm(Metadata $meta, $property, $entity = null)
    {
        $dataClass = $meta->getEntityClass();

        if (!empty($entity) && null !== $meta->getTranslationClass()) {
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

        return $this->genericFormFactory->createNamed($meta->getFormTypeName().'_property', BaseType::BASE_TYPE_CLASS, $entity, array(
            'action_type'       => 'index',
            'data_class'        => $dataClass,
            'field_filter'      => $property,
            'metadata'          => $meta,
            'validation_groups' => array(
                'Default',
                'AdminUpdateProperty',
            ),
        ));
    }
}
