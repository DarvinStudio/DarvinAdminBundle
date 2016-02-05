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

use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Menu\MenuItemInterface;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @var array
     */
    private $menuItemAttributes;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     * @param string                                       $entityClass     Entity class
     */
    public function __construct(MetadataManager $metadataManager, $entityClass)
    {
        $this->meta = $metadataManager->getMetadata($entityClass);
        $this->configuration = $this->meta->getConfiguration();
        $this->entityClass = $entityClass;
        $this->menuItemAttributes = array(
            'associated_object_class' => $this->entityClass,
            'color'                   => $this->configuration['menu']['color'],
            'description'             => $this->meta->getBaseTranslationPrefix().'menu.description',
            'homepage_menu_icon'      => $this->configuration['images']['homepage_menu_icon'],
            'index_title'             => $this->meta->getBaseTranslationPrefix().'action.index.link',
            'left_menu_icon'          => $this->configuration['images']['left_menu_icon'],
            'name'                    => $this->meta->getEntityName(),
            'new_title'               => $this->meta->getBaseTranslationPrefix().'action.new.link',
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $this->checkIfUserHasPermission(Permission::VIEW);

        list($parentEntity, $association, $associationParam, $parentEntityId) = $this->getParentEntityDefinition($request);

        $filterForm = $this->meta->isFilterFormEnabled()
            ? $this->getAdminFormFactory()->createFilterForm(
                $this->entityClass,
                $association,
                $associationParam,
                $parentEntityId
            )->handleRequest($request)
            : null;

        $qb = $this->getIndexQueryBuilder($request->getLocale(), !empty($filterForm) ? $filterForm->getData() : null);

        if ($this->meta->hasParent()) {
            $qb->andWhere(sprintf('o.%s = :%1$s', $association))->setParameter($association, $parentEntityId);
        }

        $paginatorOptions = array();

        $sortCriteria = $this->getSortCriteriaDetector()->detect($this->entityClass);

        if (!empty($sortCriteria)) {
            if ((count($sortCriteria) > 1 && !$request->query->has('sort')) || !$this->configuration['pagination']['enabled']) {
                foreach ($sortCriteria as $sort => $order) {
                    $qb->addOrderBy('o.'.$sort, $order);
                }
            } else {
                $sortFields = array_keys($sortCriteria);
                $paginatorOptions['defaultSortFieldName'] = 'o.'.$sortFields[0];
                $paginatorOptions['defaultSortDirection'] = reset($sortCriteria);
            }
        }

        $pagination = null;

        if ($this->configuration['pagination']['enabled']) {
            $this->getSortedByEntityJoiner()->joinEntity($qb, $request->query->get('sort'), $request->getLocale());

            /** @var \Knp\Component\Pager\Pagination\AbstractPagination $pagination */
            $pagination = $this->getPaginator()->paginate(
                $qb,
                $request->query->get('page', 1),
                $this->configuration['pagination']['items'],
                $paginatorOptions
            );
            $entities = $pagination->getItems();
            $entitiesCount = $pagination->getTotalItemCount();
        } else {
            $entities = $qb->getQuery()->getResult();
            $entitiesCount = count($entities);
        }
        if (isset($this->configuration['sorter'])) {
            $entities = $this->get($this->configuration['sorter']['id'])->{$this->configuration['sorter']['method']}($entities);
        }

        $this->getCustomObjectLoader()->loadCustomObjects($entities, false);

        $newFormWidget = $this->configuration['index_view_new_form'] ? $this->newAction($request, true)->getContent() : null;

        $view = $this->getEntitiesToIndexViewTransformer()->transform($entities);

        return $this->renderResponse('index', array(
            'association_param' => $associationParam,
            'entities_count'    => $entitiesCount,
            'filter_form'       => !empty($filterForm) ? $filterForm->createView() : null,
            'meta'              => $this->meta,
            'new_form_widget'   => $newFormWidget,
            'pagination'        => $pagination,
            'parent_entity'     => $parentEntity,
            'parent_entity_id'  => $parentEntityId,
            'view'              => $view,
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param bool                                      $widget  Whether to render widget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, $widget = false)
    {
        $this->checkIfUserHasPermission(Permission::CREATE_DELETE);

        list($parentEntity, $association) = $this->getParentEntityDefinition($request);

        $isXmlHttpRequest = $request->isXmlHttpRequest();

        if ($isXmlHttpRequest) {
            $widget = true;
        }

        $entityClass = $this->entityClass;
        $entity = new $entityClass();

        if ($this->meta->hasParent()) {
            $this->getPropertyAccessor()->setValue($entity, $association, $parentEntity);
        }
        if ($this->getTranslationJoiner()->isTranslatable($entityClass)) {
            $this->getTranslationsInitializer()->initializeTranslations($entity, $this->getParameter('darvin_admin.locales'));
        }

        $form = $this->getAdminFormFactory()->createEntityForm(
            $entity,
            'new',
            $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_NEW),
            $widget ? array(AdminFormFactory::SUBMIT_INDEX) : $this->getEntityFormSubmitButtons()
        )->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response($this->renderNewTemplate($widget, $form, $parentEntity));
        }

        $success = $form->isValid();

        if ($success) {
            $em = $this->getEntityManager();
            $em->persist($entity);
            $em->flush();

            $html = '';
            $message = $this->meta->getBaseTranslationPrefix().'action.new.success';
        } else {
            $html = $this->renderNewTemplate($widget, $form, $parentEntity);
            $message = FlashNotifierInterface::MESSAGE_FORM_ERROR;
        }
        if ($isXmlHttpRequest) {
            return new AjaxResponse($html, $success, $message, array(), $success ? '' : null);
        }

        $this->getFlashNotifier()->done($success, $message);

        return $success
            ? $this->successRedirect($form, $entity)
            : new Response($this->renderNewTemplate($widget, $form, $parentEntity));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function copyAction(Request $request, $id)
    {
        $this->checkIfUserHasPermission(Permission::CREATE_DELETE);

        $entity = $this->getEntity($id);

        $form = $this->getAdminFormFactory()->createCopyForm($entity)->handleRequest($request);

        $this->getFormHandler()->handleCopyForm($form, $entity);

        $url = $request->headers->get('referer', $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_INDEX));

        return new RedirectResponse($url);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        $this->checkIfUserHasPermission(Permission::EDIT);

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->getEntity($id);

        $form = $this->getAdminFormFactory()->createEntityForm(
            $entity,
            'edit',
            $this->getAdminRouter()->generate($entity, AdminRouter::TYPE_EDIT),
            $this->getEntityFormSubmitButtons()
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

        $this->checkIfUserHasPermission(Permission::EDIT);

        $entity = $this->getEntity($id);

        $form = $this->getAdminFormFactory()->createPropertyForm($this->meta, $property, $entity);

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
                : FlashNotifierInterface::MESSAGE_FORM_ERROR
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
        $this->checkIfUserHasPermission(Permission::VIEW);

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->getEntity($id);

        $this->getCustomObjectLoader()->loadCustomObjects($entity, false);

        $view = $this->getEntityToShowViewTransformer()->transform($entity);

        return $this->renderResponse('show', array(
            'entity'        => $entity,
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
            'view'          => $view,
        ), $request->isXmlHttpRequest());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $this->checkIfUserHasPermission(Permission::CREATE_DELETE);

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
    public function setChildMenuItems(array $childMenuItems)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getChildMenuItems()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexUrl()
    {
        if (!$this->isGranted(Permission::VIEW, $this->entityClass)) {
            return null;
        }

        return $this->getAdminRouter()->isRouteExists($this->entityClass, AdminRouter::TYPE_INDEX)
            ? $this->getAdminRouter()->generate($this->entityClass, AdminRouter::TYPE_INDEX)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewUrl()
    {
        if (!$this->isGranted(Permission::CREATE_DELETE, $this->entityClass)) {
            return null;
        }

        return $this->getAdminRouter()->isRouteExists($this->entityClass, AdminRouter::TYPE_NEW)
            ? $this->getAdminRouter()->generate($this->entityClass, AdminRouter::TYPE_NEW)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMenuItemAttributes(array $menuItemAttributes)
    {
        $this->menuItemAttributes = $menuItemAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuItemAttributes()
    {
        return $this->menuItemAttributes;
    }

    /**
     * @param string $locale         Locale
     * @param array  $filterFormData Filter form data
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getIndexQueryBuilder($locale, array $filterFormData = null)
    {
        $qb = $this->getEntityManager()->getRepository($this->entityClass)->createQueryBuilder('o');

        foreach ($this->configuration['joins'] as $alias => $join) {
            if (false === strpos($join, '.')) {
                $join = 'o.'.$join;
            }

            $qb->addSelect($alias)->leftJoin($join, $alias);
        }

        $translationJoiner = $this->getTranslationJoiner();

        if ($translationJoiner->isTranslatable($this->entityClass)) {
            $translationJoiner->joinTranslation($qb, $locale, 'translations');
            $qb->addSelect('translations');
        }
        if (empty($filterFormData)) {
            return $qb;
        }

        $filtererOptions = array('non_strict_comparison_fields' => array());
        $getNonStrictComparisonFields = function (array $fields) use (&$filtererOptions) {
            foreach ($fields as $field => $attr) {
                if (!$attr['compare_strict']) {
                    $filtererOptions['non_strict_comparison_fields'][] = $field;
                }
            }
        };
        $getNonStrictComparisonFields($this->configuration['form']['filter']['fields']);
        array_map($getNonStrictComparisonFields, $this->configuration['form']['filter']['field_groups']);

        $this->getFilterer()->filter($qb, $filterFormData, $filtererOptions);

        return $qb;
    }

    /**
     * @param string $permission Permission
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    private function checkIfUserHasPermission($permission)
    {
        if (!$this->isGranted($permission, $this->entityClass)) {
            throw $this->createAccessDeniedException(
                sprintf('You do not have "%s" permission on "%s" class objects.', $permission, $this->entityClass)
            );
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getParentEntityDefinition(Request $request)
    {
        if (!$this->meta->hasParent()) {
            return array_fill(0, 4, null);
        }

        $associationParam = $this->meta->getParent()->getAssociationParameterName();
        $id = (int) $request->query->get($associationParam);

        if (empty($id)) {
            throw $this->createNotFoundException(sprintf('Value of query parameter "%s" must be provided.', $associationParam));
        }

        return array(
            $this->getEntity($id, $this->meta->getParent()->getMetadata()->getEntityClass()),
            $this->meta->getParent()->getAssociation(),
            $associationParam,
            $id,
        );
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
     * @return array
     */
    private function getEntityFormSubmitButtons()
    {
        $submitButtons = array();

        $adminRouter = $this->getAdminRouter();

        foreach (self::$submitButtonRedirects as $submitButton => $routeType) {
            if ($adminRouter->isRouteExists($this->entityClass, $routeType)) {
                $submitButtons[] = $submitButton;
            }
        }

        return $submitButtons;
    }

    /**
     * @param bool                                  $widget       Whether to render widget
     * @param \Symfony\Component\Form\FormInterface $form         Entity form
     * @param object                                $parentEntity Parent entity
     *
     * @return string
     */
    private function renderNewTemplate($widget, FormInterface $form, $parentEntity)
    {
        return $this->renderTemplate('new', array(
            'ajax_form'     => $widget,
            'form'          => $form->createView(),
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
        ), $widget);
    }

    /**
     * @param string $viewType       View type
     * @param array  $templateParams Template parameters
     * @param bool   $widget         Whether to render widget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderResponse($viewType, array $templateParams = array(), $widget = false)
    {
        return new Response($this->renderTemplate($viewType, $templateParams, $widget));
    }

    /**
     * @param string $viewType       View type
     * @param array  $templateParams Template parameters
     * @param bool   $widget         Whether to render widget
     *
     * @return string
     */
    private function renderTemplate($viewType, array $templateParams = array(), $widget = false)
    {
        $template = $widget && !empty($this->configuration['view'][$viewType]['template'])
            ? $this->configuration['view'][$viewType]['template']
            : sprintf('DarvinAdminBundle:Crud%s:%s.html.twig', $widget ? '/widget' : '', $viewType);

        return $this->renderView($template, $templateParams);
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
        return $this->get('darvin_admin.form.factory');
    }

    /** @return \Darvin\AdminBundle\Route\AdminRouter */
    private function getAdminRouter()
    {
        return $this->get('darvin_admin.route.router');
    }

    /** @return \Darvin\Utils\CustomObject\CustomObjectLoaderInterface */
    private function getCustomObjectLoader()
    {
        return $this->get('darvin_utils.custom_object.loader');
    }

    /** @return \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer */
    private function getEntitiesToIndexViewTransformer()
    {
        return $this->get('darvin_admin.view.entity_transformer.index');
    }

    /** @return \Darvin\AdminBundle\View\Show\EntityToShowViewTransformer */
    private function getEntityToShowViewTransformer()
    {
        return $this->get('darvin_admin.view.entity_transformer.show');
    }

    /** @return \Doctrine\ORM\EntityManager */
    private function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /** @return \Darvin\ContentBundle\Filterer\FiltererInterface */
    private function getFilterer()
    {
        return $this->get('darvin_content.filterer');
    }

    /** @return \Darvin\Utils\Flash\FlashNotifierInterface */
    private function getFlashNotifier()
    {
        return $this->get('darvin_utils.flash.notifier');
    }

    /** @return \Darvin\AdminBundle\Form\FormHandler */
    private function getFormHandler()
    {
        return $this->get('darvin_admin.form.handler');
    }

    /** @return \Knp\Component\Pager\Paginator */
    private function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /** @return \Symfony\Component\PropertyAccess\PropertyAccessorInterface */
    private function getPropertyAccessor()
    {
        return $this->get('property_accessor');
    }

    /** @return \Darvin\AdminBundle\Metadata\SortCriteriaDetector */
    private function getSortCriteriaDetector()
    {
        return $this->get('darvin_admin.metadata.sort_criteria_detector');
    }

    /** @return \Darvin\ContentBundle\Sorting\SortedByEntityJoinerInterface */
    private function getSortedByEntityJoiner()
    {
        return $this->get('darvin_content.sorting.sorted_by_entity_joiner');
    }

    /** @return \Darvin\ContentBundle\Translatable\TranslationJoinerInterface */
    private function getTranslationJoiner()
    {
        return $this->get('darvin_content.translatable.translation_joiner');
    }

    /** @return \Darvin\ContentBundle\Translatable\TranslationsInitializer */
    private function getTranslationsInitializer()
    {
        return $this->get('darvin_content.translatable.translations_initializer');
    }
}
