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
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

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
    private $formFactory;

    /**
     * @var \Symfony\Component\Form\FormTypeGuesserInterface
     */
    private $formTypeGuesser;

    /**
     * @var \Darvin\AdminBundle\Metadata\IdentifierAccessor
     */
    private $identifierAccessor;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouter            $adminRouter        Admin router
     * @param \Symfony\Component\Form\FormFactoryInterface     $formFactory        Form factory
     * @param \Symfony\Component\Form\FormTypeGuesserInterface $formTypeGuesser    Form type guesser
     * @param \Darvin\AdminBundle\Metadata\IdentifierAccessor  $identifierAccessor Identifier accessor
     * @param \Darvin\AdminBundle\Metadata\MetadataManager     $metadataManager    Metadata manager
     */
    public function __construct(
        AdminRouter $adminRouter,
        FormFactoryInterface $formFactory,
        FormTypeGuesserInterface $formTypeGuesser,
        IdentifierAccessor $identifierAccessor,
        MetadataManager $metadataManager
    ) {
        $this->adminRouter = $adminRouter;
        $this->formFactory = $formFactory;
        $this->formTypeGuesser = $formTypeGuesser;
        $this->identifierAccessor = $identifierAccessor;
        $this->metadataManager = $metadataManager;
    }

    /**
     * @param object $entity Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCopyForm($entity)
    {
        return $this->createIdForm($entity, 'copy_', $this->adminRouter->generate($entity, AdminRouter::TYPE_COPY));
    }

    /**
     * @param object $entity Entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createDeleteForm($entity)
    {
        return $this->createIdForm($entity, 'delete_', $this->adminRouter->generate($entity, AdminRouter::TYPE_DELETE));
    }

    /**
     * @param object $entity        Entity
     * @param string $action        Action
     * @param string $formAction    Form action
     * @param array  $submitButtons Submit button names
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEntityForm($entity, $action, $formAction, array $submitButtons)
    {
        $meta = $this->metadataManager->getMetadata($entity);
        $configuration = $meta->getConfiguration();

        $type = $configuration['form'][$action]['type'];

        if (empty($type)) {
            $type = new BaseType($action, $meta);
        }

        $builder = $this->formFactory->createBuilder($type, $entity, array(
            'action'             => $formAction,
            'translation_domain' => 'admin',
            'validation_groups'  => array(
                'Default',
                ucfirst($action),
            ),
        ));

        foreach ($submitButtons as $name) {
            $builder->add($name, 'submit', array(
                'label' => self::$submitButtons[$name],
            ));
        }

        return $builder->getForm();
    }

    /**
     * @param string $entityClass             Entity class
     * @param string $parentEntityAssociation Parent entity association
     * @param mixed  $parentEntityId          Parent entity ID
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createFilterForm($entityClass, $parentEntityAssociation = null, $parentEntityId = null)
    {
        $meta = $this->metadataManager->getMetadata($entityClass);

        if (!$meta->isFilterFormEnabled() || !$this->adminRouter->isRouteExists($entityClass, AdminRouter::TYPE_INDEX)) {
            return null;
        }

        $configuration = $meta->getConfiguration();

        $type = $configuration['form']['filter']['type'];

        if (empty($type)) {
            $type = new FilterType($this->formTypeGuesser, $meta);
        }

        $actionRouteParams = !empty($parentEntityAssociation)
            ? array(
                $parentEntityAssociation => $parentEntityId,
            )
            : array();
        $action = $this->adminRouter->generate($entityClass, AdminRouter::TYPE_INDEX, $actionRouteParams);

        return $this->formFactory->create($type, null, array(
            'action'                    => $action,
            'parent_entity_association' => $parentEntityAssociation,
            'parent_entity_id'          => $parentEntityId,
        ));
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

        $builder = $this->formFactory->createNamedBuilder(
            $namePrefix.$id,
            'form',
            array(
                'id' => $id,
            ),
            array(
                'action'             => $action,
                'intention'          => md5(__FILE__.ClassUtils::getClass($entity).$id),
                'translation_domain' => 'admin',
            )
        )->add('id', 'hidden');

        return $builder->getForm();
    }
}
