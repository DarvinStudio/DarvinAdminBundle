<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\Flash\FlashNotifier;
use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Form\Type\BaseType;
use Darvin\AdminBundle\Menu\MenuItemInterface;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CRUD controller
 */
class CrudController extends Controller implements MenuItemInterface
{
    /**
     * @var array
     */
    private static $submitButtonRedirects = array(
        AdminFormFactory::SUBMIT_EDIT  => AdminRouter::TYPE_EDIT,
        AdminFormFactory::SUBMIT_INDEX => AdminRouter::TYPE_INDEX,
        AdminFormFactory::SUBMIT_NEW   => AdminRouter::TYPE_NEW,
    );

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $meta;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     * @param string                                       $entityClass     Entity class
     */
    public function __construct(MetadataManager $metadataManager, $entityClass)
    {
        $this->meta = $metadataManager->getByEntityClass($entityClass);
        $this->configuration = $this->meta->getConfiguration();
        $this->entityClass = $entityClass;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        list($parentEntity, $association, $parentEntityId) = $this->getParentEntityDefinition($request);

        $qb = $this->getEntityManager()->getRepository($this->entityClass)->createQueryBuilder('o');

        if ($this->meta->hasParent()) {
            $qb->where(sprintf('o.%s = :%1$s', $association))->setParameter($association, $parentEntityId);
        }

        $paginatorOptions = array();

        $sortCriteria = $this->getSortCriteriaDetector()->detect($this->entityClass);

        if (!empty($sortCriteria)) {
            if (count($sortCriteria) > 1) {
                foreach ($sortCriteria as $sort => $order) {
                    $qb->addOrderBy('o.'.$sort, $order);
                }
            } else {
                $sortFields = array_keys($sortCriteria);
                $paginatorOptions['defaultSortFieldName'] = 'o.'.$sortFields[0];
                $paginatorOptions['defaultSortDirection'] = reset($sortCriteria);
            }
        }

        /** @var \Knp\Component\Pager\Pagination\AbstractPagination $pagination */
        $pagination = $this->getPaginator()->paginate(
            $qb,
            $request->query->get('page', 1),
            $this->configuration['pagination_items'],
            $paginatorOptions
        );

        $view = $this->getEntitiesToIndexViewTransformer()->transform($pagination->getItems());

        return $this->renderResponse('index', array(
            'meta'          => $this->meta,
            'pagination'    => $pagination,
            'parent_entity' => $parentEntity,
            'view'          => $view,
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        list($parentEntity, $association) = $this->getParentEntityDefinition($request);

        $entityClass = $this->entityClass;
        $entity = new $entityClass();

        if ($this->meta->hasParent()) {
            $this->getPropertyAccessor()->setValue($entity, $association, $parentEntity);
        }

        $form = $this->getAdminFormFactory()->createEntityForm(
            $entity,
            'new',
            $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_NEW)
        )->handleRequest($request);

        return $this->getFormHandler()->handleEntityForm($form, 'action.new.success')
            ? $this->successRedirect($form, $entity)
            : $this->renderResponse('new', array(
                'form'          => $form->createView(),
                'meta'          => $this->meta,
                'parent_entity' => $parentEntity,
            ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->getEntity($id);

        $form = $this->getAdminFormFactory()->createEntityForm(
            $entity,
            'edit',
            $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_EDIT)
        )->handleRequest($request);

        return $this->getFormHandler()->handleEntityForm($form, 'action.edit.success')
            ? $this->successRedirect($form, $entity)
            : $this->renderResponse('edit', array(
                'entity'        => $entity,
                'form'          => $form->createView(),
                'meta'          => $this->meta,
                'parent_entity' => $parentEntity,
            ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request  Request
     * @param int                                       $id       Entity ID
     * @param string                                    $property Property to update
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function updatePropertyAction(Request $request, $id, $property)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only XMLHttpRequests are allowed.');
        }

        $entity = $this->getEntity($id);

        $form = $this->createForm(new BaseType('index', $this->meta, $property), $entity);

        $originalValue = $form->createView()->children[$property]->vars['value'];

        $formIsValid = $form->handleRequest($request)->isValid();

        if ($formIsValid) {
            $this->getEntityManager()->flush();
        }

        $formView = $form->createView();

        if ($formIsValid) {
            $originalValue = $formView->children[$property]->vars['value'];
        }

        return new JsonResponse(array(
            'form' => $this->getEntitiesToIndexViewTransformer()->renderPropertyForm($form, $entity, $property, array(
                'original_value' => $originalValue,
            )),
            'message' => $formIsValid
                ? $this->meta->getBaseTranslationPrefix().'action.update_property.success'
                : FlashNotifier::MESSAGE_FORM_ERROR
            ,
            'success' => $formIsValid,
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $id)
    {
        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->getEntity($id);

        $view = $this->getEntityToShowViewTransformer()->transform($entity);

        return $this->renderResponse('show', array(
            'entity'        => $entity,
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
            'view'          => $view,
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $this->getParentEntityDefinition($request);

        $entity = $this->getEntity($id);

        $form = $this->getAdminFormFactory()->createDeleteForm($entity)->handleRequest($request);

        $url = $this->getFormHandler()->handleDeleteForm($form, $entity)
            ? $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_INDEX)
            : $request->headers->get(
                'referer',
                $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_INDEX)
            );

        return $this->redirect($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexUrl()
    {
        return $this->getAdminRouter()->isRouteExists($this->entityClass, AdminRouter::TYPE_INDEX)
            ? $this->getAdminRouter()->generate($this->entityClass, AdminRouter::TYPE_INDEX)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewUrl()
    {
        return $this->getAdminRouter()->isRouteExists($this->entityClass, AdminRouter::TYPE_NEW)
            ? $this->getAdminRouter()->generate($this->entityClass, AdminRouter::TYPE_NEW)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuTitle()
    {
        return $this->meta->getBaseTranslationPrefix().'action.index.link';
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getParentEntityDefinition(Request $request)
    {
        $association = $id = $entity = null;

        if (!$this->meta->hasParent()) {
            return array($association, $id, $entity);
        }

        $association = $this->meta->getParent()->getAssociation();
        $id = (int) $request->query->get($association);

        if (empty($id)) {
            throw new NotFoundHttpException(sprintf('Value of query parameter "%s" must be provided.', $association));
        }

        return array($this->getEntity($id, $this->meta->getParent()->getMetadata()->getEntityClass()), $association, $id);
    }

    /**
     * @param int    $id          Entity ID
     * @param string $entityClass Entity class
     *
     * @return object
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getEntity($id, $entityClass = null)
    {
        if (empty($entityClass)) {
            $entityClass = $this->entityClass;
        }

        $entity = $this->getEntityManager()->find($entityClass, $id);

        if (empty($entity)) {
            throw $this->createNotFoundException(
                sprintf('Unable to find entity "%s" by ID "%d".', $entityClass, $id)
            );
        }

        return $entity;
    }

    /**
     * @param string $viewType       View type
     * @param array  $templateParams Template parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderResponse($viewType, array $templateParams = array())
    {
        $template = !empty($this->configuration['view'][$viewType]['template'])
            ? $this->configuration['view'][$viewType]['template']
            : sprintf('DarvinAdminBundle:Crud:%s.html.twig', $viewType);

        return $this->render($template, $templateParams);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form   Form
     * @param object                                $entity Entity
     *
     * @return string
     */
    private function successRedirect(FormInterface $form, $entity)
    {
        foreach ($form->all() as $name => $child) {
            if ($child instanceof ClickableInterface && $child->isClicked() && isset(self::$submitButtonRedirects[$name])) {
                return $this->redirect($this->getAdminRouter()->generate($entity, self::$submitButtonRedirects[$name]));
            }
        }

        return $this->redirect($this->getAdminRouter()->generate($entity, AdminRouter::TYPE_EDIT));
    }





    /** @return \Darvin\AdminBundle\Form\AdminFormFactory */
    private function getAdminFormFactory()
    {
        return $this->container->get('darvin_admin.form.factory');
    }

    /** @return \Darvin\AdminBundle\Route\AdminRouter */
    private function getAdminRouter()
    {
        return $this->container->get('darvin_admin.route.router');
    }

    /** @return \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer */
    private function getEntitiesToIndexViewTransformer()
    {
        return $this->container->get('darvin_admin.view.entity_transformer.index');
    }

    /** @return \Darvin\AdminBundle\View\Show\EntityToShowViewTransformer */
    private function getEntityToShowViewTransformer()
    {
        return $this->container->get('darvin_admin.view.entity_transformer.show');
    }

    /** @return \Doctrine\ORM\EntityManager */
    private function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /** @return \Darvin\AdminBundle\Form\FormHandler */
    private function getFormHandler()
    {
        return $this->container->get('darvin_admin.form.handler');
    }

    /** @return \Knp\Component\Pager\Paginator */
    private function getPaginator()
    {
        return $this->container->get('knp_paginator');
    }

    /** @return \Symfony\Component\PropertyAccess\PropertyAccessorInterface */
    private function getPropertyAccessor()
    {
        return $this->container->get('property_accessor');
    }

    /** @return \Darvin\AdminBundle\Metadata\SortCriteriaDetector */
    private function getSortCriteriaDetector()
    {
        return $this->container->get('darvin_admin.metadata.sort_criteria_detector');
    }
}
