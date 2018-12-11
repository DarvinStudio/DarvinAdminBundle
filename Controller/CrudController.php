<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CopiedEvent;
use Darvin\AdminBundle\Event\Crud\CreatedEvent;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\DeletedEvent;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Route\AdminRouter;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\BatchDeleteWidget;
use Darvin\Utils\CustomObject\CustomObjectException;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * CRUD controller
 */
class CrudController extends Controller
{
    private const SUBMIT_BUTTON_REDIRECTS = [
        AdminFormFactory::SUBMIT_EDIT  => AdminRouter::TYPE_EDIT,
        AdminFormFactory::SUBMIT_INDEX => AdminRouter::TYPE_INDEX,
        AdminFormFactory::SUBMIT_NEW   => AdminRouter::TYPE_NEW,
    ];

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
    public function __construct(MetadataManager $metadataManager, string $entityClass)
    {
        $this->meta = $metadataManager->getMetadata($entityClass);
        $this->configuration = $this->meta->getConfiguration();
        $this->entityClass = $entityClass;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request): Response
    {
        $this->checkPermission(Permission::VIEW);

        list($parentEntity, $association, $associationParam, $parentEntityId) = $this->getParentEntityDefinition($request);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__));

        $filterForm = null;

        if ($this->meta->isFilterFormEnabled()) {
            $filterForm = $this->getAdminFormFactory()->createFilterForm($this->meta, $associationParam, $parentEntityId)->handleRequest($request);
        }

        $qb = $this->getIndexQueryBuilder($request->getLocale(), !empty($filterForm) ? $filterForm->getData() : null);

        if ($this->getUserQueryBuilderFilterer()->isFilterable($qb)) {
            $this->getUserQueryBuilderFilterer()->filter($qb);
        }
        if ($this->meta->hasParent()) {
            $qb->andWhere(sprintf('o.%s = :%1$s', $association))->setParameter($association, $parentEntityId);
        }

        $batchDeleteForm  = null;
        $newFormWidget    = null;
        $sortCriteria     = $this->getSortCriteriaDetector()->detect($this->entityClass);
        $pagination       = null;
        $paginatorOptions = [
            'wrap-queries' => true,
        ];

        if (!empty($sortCriteria)) {
            if ((count($sortCriteria) > 1 && !$request->query->has('sort')) || !$this->configuration['pagination']['enabled']) {
                foreach ($sortCriteria as $sort => $order) {
                    $qb->addOrderBy('o.'.$sort, $order);
                }
            } else {
                $sortField = array_keys($sortCriteria)[0];

                if (false === strpos($sortField, '.')) {
                    $sortField = sprintf('o.%s', $sortField);
                }

                $paginatorOptions['defaultSortFieldName'] = $sortField;
                $paginatorOptions['defaultSortDirection'] = reset($sortCriteria);
            }
        }
        if ($this->configuration['pagination']['enabled']) {
            $this->getSortedByEntityJoiner()->joinEntity($qb, $request->query->get('sort'), $request->getLocale());

            $page = $request->query->get('page', 1);

            /** @var \Knp\Component\Pager\Pagination\AbstractPagination $pagination */
            $pagination = $this->getPaginator()->paginate($qb, $page, $this->configuration['pagination']['items'], $paginatorOptions);

            $entities    = $page > 0 ? $pagination->getItems() : $qb->getQuery()->getResult();
            $entityCount = $pagination->getTotalItemCount();
        } else {
            $entities = $qb->getQuery()->getResult();

            $entityCount = count($entities);
        }
        if (isset($this->configuration['sorter'])) {
            $entities = $this->get($this->configuration['sorter']['id'])->{$this->configuration['sorter']['method']}($entities);
        }
        try {
            $this->getCustomObjectLoader()->loadCustomObjects($entities);
        } catch (CustomObjectException $ex) {
        }
        if (!empty($entities)
            && $this->isGranted(Permission::CREATE_DELETE, $this->entityClass)
            && $this->getAdminRouter()->exists($this->entityClass, AdminRouter::TYPE_BATCH_DELETE)
            && isset($this->configuration['view']['index']['action_widgets'][BatchDeleteWidget::ALIAS])
        ) {
            $batchDeleteForm = $this->getAdminFormFactory()->createBatchDeleteForm($this->entityClass, $entities)->createView();
        }
        if ($this->configuration['index_view_new_form']) {
            $newFormWidget = $this->newAction($request, true)->getContent();
        }

        $view = $this->getEntitiesToIndexViewTransformer()->transform($this->meta, $entities);

        return $this->renderResponse('index', [
            'association_param' => $associationParam,
            'batch_delete_form' => $batchDeleteForm,
            'entity_count'      => $entityCount,
            'filter_form'       => !empty($filterForm) ? $filterForm->createView() : null,
            'meta'              => $this->meta,
            'new_form_widget'   => $newFormWidget,
            'pagination'        => $pagination,
            'parent_entity'     => $parentEntity,
            'parent_entity_id'  => $parentEntityId,
            'view'              => $view,
        ], $request->isXmlHttpRequest());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param bool                                      $widget  Is widget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, bool $widget = false): Response
    {
        if ((bool)$request->query->get('widget')) {
            $widget = true;
        }

        $this->checkPermission(Permission::CREATE_DELETE);

        list($parentEntity, $association) = $this->getParentEntityDefinition($request);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__));

        $entityClass = $this->entityClass;

        $entity = new $entityClass();

        if ($this->meta->hasParent()) {
            $this->getPropertyAccessor()->setValue($entity, $association, $parentEntity);
        }
        if ($this->getTranslationJoiner()->isTranslatable($entityClass)) {
            $this->getTranslationsInitializer()->initializeTranslations($entity, $this->getParameter('darvin_admin.locales'));
        }

        $this->getNewActionFilterFormHandler()->handleForm($entity, $request);

        $form = $this->getAdminFormFactory()->createEntityForm(
            $this->meta,
            $entity,
            'new',
            $this->getAdminRouter()->generate($entity, $entityClass, AdminRouter::TYPE_NEW, [
                'widget' => $widget,
            ]),
            $widget ? [AdminFormFactory::SUBMIT_INDEX] : $this->getEntityFormSubmitButtons()
        )->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response($this->renderNewTemplate($form, $parentEntity, $widget, $request->isXmlHttpRequest()));
        }
        if (!$form->isValid()) {
            if (!$request->isXmlHttpRequest()) {
                $this->getFlashNotifier()->formError();
            }

            $html = $this->renderNewTemplate($form, $parentEntity, $widget, $request->isXmlHttpRequest());

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($html, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
            }

            return new Response($html);
        }

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        $this->getEventDispatcher()->dispatch(CrudEvents::CREATED, new CreatedEvent($this->meta, $this->getUser(), $entity));

        $message     = sprintf('%saction.new.success', $this->meta->getBaseTranslationPrefix());
        $redirectUrl = $this->successRedirect($form, $entity);

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse(null, true, $message, [], $redirectUrl);
        }

        $this->getFlashNotifier()->success($message);

        return $this->redirect($redirectUrl);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function copyAction(Request $request, $id)
    {
        $this->checkPermission(Permission::CREATE_DELETE);

        $entity = $this->findEntity($id);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__, $entity));

        $form = $this->getAdminFormFactory()->createCopyForm($entity, $this->entityClass)->handleRequest($request);

        $copy = $this->getFormHandler()->handleCopyForm($form, $entity);

        if (!empty($copy)) {
            $this->getEventDispatcher()->dispatch(CrudEvents::COPIED, new CopiedEvent($this->meta, $this->getUser(), $entity, $copy));
        }

        $url = $request->headers->get(
            'referer',
            $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouter::TYPE_INDEX)
        );

        return new RedirectResponse($url);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): Response
    {
        $this->checkPermission(Permission::EDIT);

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($id);

        $entityBefore = clone $entity;

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__, $entity));

        $form = $this->getAdminFormFactory()->createEntityForm(
            $this->meta,
            $entity,
            'edit',
            $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouter::TYPE_EDIT),
            $this->getEntityFormSubmitButtons()
        )->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response($this->renderEditTemplate($entity, $form, $parentEntity, $request->isXmlHttpRequest()));
        }
        if (!$form->isValid()) {
            if (!$request->isXmlHttpRequest()) {
                $this->getFlashNotifier()->formError();
            }

            $html = $this->renderEditTemplate($entity, $form, $parentEntity, $request->isXmlHttpRequest());

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($html, false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
            }

            return new Response($html);
        }

        $this->getEntityManager()->flush();

        $this->getEventDispatcher()->dispatch(CrudEvents::UPDATED, new UpdatedEvent($this->meta, $this->getUser(), $entityBefore, $entity));

        $message     = sprintf('%saction.edit.success', $this->meta->getBaseTranslationPrefix());
        $redirectUrl = $this->successRedirect($form, $entity);

        if ($request->isXmlHttpRequest()) {
            $html = null;

            if ($redirectUrl === $request->getRequestUri()) {
                $html        = $this->renderEditTemplate($entity, $form, $parentEntity, true);
                $redirectUrl = null;
            }

            return new AjaxResponse($html, true, $message, [], $redirectUrl);
        }

        $this->getFlashNotifier()->success($message);

        return $this->redirect($redirectUrl);
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

        $this->checkPermission(Permission::EDIT);

        $entity = $this->findEntity($id);

        $entityBefore = clone $entity;

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__, $entity));

        $form = $this->getAdminFormFactory()->createPropertyForm($this->meta, $property, $entity)->handleRequest($request);

        $success = $form->isValid();

        $message = 'flash.success.update_property';

        if ($success) {
            $this->getEntityManager()->flush();

            $this->getEventDispatcher()->dispatch(CrudEvents::UPDATED, new UpdatedEvent($this->meta, $this->getUser(), $entityBefore, $entity));

            $form = $this->getAdminFormFactory()->createPropertyForm($this->meta, $property, $entity);
        } else {
            $prefix     = $this->meta->getEntityTranslationPrefix();
            $translator = $this->getTranslator();

            $message = implode('<br>', array_map(function (FormError $error) use ($prefix, $translator) {
                $message = $error->getMessage();

                /** @var \Symfony\Component\Validator\ConstraintViolation|null $cause */
                $cause = $error->getCause();

                if (!empty($cause)) {
                    $translation = preg_replace('/^data\./', $prefix, StringsUtil::toUnderscore($cause->getPropertyPath()));

                    $translated = $translator->trans($translation, [], 'admin');

                    if ($translated !== $translation) {
                        $message = sprintf('%s: %s', $translated, $message);
                    }
                }

                return $message;
            }, iterator_to_array($form->getErrors(true))));
        }

        return new JsonResponse([
            'html'    => $this->getEntitiesToIndexViewTransformer()->renderPropertyForm($form, $entityBefore, $this->entityClass, $property),
            'message' => $message,
            'success' => $success,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, $id)
    {
        $this->checkPermission(Permission::VIEW);

        list($parentEntity) = $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($id);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__, $entity));

        try {
            $this->getCustomObjectLoader()->loadCustomObjects($entity);
        } catch (CustomObjectException $ex) {
        }

        $view = $this->getEntityToShowViewTransformer()->transform($this->meta, $entity);

        return $this->renderResponse('show', [
            'entity'        => $entity,
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
            'view'          => $view,
        ], $request->isXmlHttpRequest());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $id): Response
    {
        $this->checkPermission(Permission::CREATE_DELETE);

        $this->getParentEntityDefinition($request);

        $entity = $this->findEntity($id);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__, $entity));

        $form = $this->getAdminFormFactory()->createDeleteForm($entity, $this->entityClass)->handleRequest($request);

        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($this->renderView('@DarvinAdmin/widget/delete_form.html.twig', [
                    'form'               => $this->getAdminFormFactory()->createDeleteForm($entity, $this->entityClass)->createView(),
                    'translation_prefix' => $this->meta->getBaseTranslationPrefix(),
                ]), false, $message);
            }

            $this->getFlashNotifier()->error($message);

            return $this->redirect($request->headers->get(
                'referer',
                $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouter::TYPE_INDEX)
            ));
        }

        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();

        $this->getEventDispatcher()->dispatch(CrudEvents::DELETED, new DeletedEvent($this->meta, $this->getUser(), $entity));

        $message     = sprintf('%saction.delete.success', $this->meta->getBaseTranslationPrefix());
        $redirectUrl = $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouter::TYPE_INDEX);

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse(null, true, $message, [], $redirectUrl);
        }

        $this->getFlashNotifier()->success($message);

        return $this->redirect($redirectUrl);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     */
    public function batchDeleteAction(Request $request)
    {
        $this->checkPermission(Permission::CREATE_DELETE);

        $this->getParentEntityDefinition($request);

        $this->getEventDispatcher()->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->getUser(), __FUNCTION__));

        $form = $this->getAdminFormFactory()->createBatchDeleteForm($this->entityClass)->handleRequest($request);
        $entities = $form->get('entities')->getData();

        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        }
        if (empty($entities)) {
            throw new \RuntimeException(
                sprintf('Unable to handle batch delete form for entity class "%s": entity array is empty.', $this->entityClass)
            );
        }
        if ($this->getFormHandler()->handleBatchDeleteForm($form, $entities)) {
            $eventDispatcher = $this->getEventDispatcher();
            $user            = $this->getUser();

            foreach ($entities as $entity) {
                $eventDispatcher->dispatch(CrudEvents::DELETED, new DeletedEvent($this->meta, $user, $entity));
            }

            return $this->redirect($this->getAdminRouter()->generate(reset($entities), $this->entityClass, AdminRouter::TYPE_INDEX));
        }

        $url = $request->headers->get(
            'referer',
            $this->getAdminRouter()->generate(reset($entities), $this->entityClass, AdminRouter::TYPE_INDEX)
        );

        return $this->redirect($url);
    }

    /**
     * @param string $locale         Locale
     * @param array  $filterFormData Filter form data
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getIndexQueryBuilder(string $locale, array $filterFormData = null): QueryBuilder
    {
        $qb = $this->getEntityManager()->getRepository($this->entityClass)->createQueryBuilder('o');

        foreach ($this->configuration['joins'] as $alias => $join) {
            if (false === strpos($join, '.')) {
                $join = 'o.'.$join;
            }

            $qb->addSelect($alias)->leftJoin($join, $alias);
        }
        if ($this->getTranslationJoiner()->isTranslatable($this->entityClass)) {
            $this->getTranslationJoiner()->joinTranslation($qb, true, $locale);
        }
        if (empty($filterFormData)) {
            return $qb;
        }

        $filtererOptions = [
            'non_strict_comparison_fields' => [],
        ];

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
    private function checkPermission(string $permission): void
    {
        if (!$this->isGranted($permission, $this->entityClass)) {
            throw $this->createAccessDeniedException(
                sprintf('You do not have "%s" permission on "%s" class objects.', $permission, $this->entityClass)
            );
        }
    }

    /**
     * @param int    $id    Entity ID
     * @param string $class Entity class
     *
     * @return object
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findEntity($id, ?string $class = null)
    {
        if (empty($class)) {
            $class = $this->entityClass;
        }

        $entity = $this->getEntityManager()->find($class, $id);

        if (empty($entity)) {
            throw $this->createNotFoundException(sprintf('Unable to find entity "%s" by ID "%s".', $class, $id));
        }

        return $entity;
    }

    /**
     * @return array
     */
    private function getEntityFormSubmitButtons(): array
    {
        $buttons = [];
        $router  = $this->getAdminRouter();

        foreach (static::SUBMIT_BUTTON_REDIRECTS as $button => $routeType) {
            if ($router->exists($this->entityClass, $routeType)) {
                $buttons[] = $button;
            }
        }

        return $buttons;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getParentEntityDefinition(Request $request): array
    {
        if (!$this->meta->hasParent()) {
            return array_fill(0, 4, null);
        }

        $associationParam = $this->meta->getParent()->getAssociationParameterName();

        $id = $request->query->get($associationParam);

        if (empty($id)) {
            throw $this->createNotFoundException(sprintf('Value of query parameter "%s" must be provided.', $associationParam));
        }

        return [
            $this->findEntity($id, $this->meta->getParent()->getMetadata()->getEntityClass()),
            $this->meta->getParent()->getAssociation(),
            $associationParam,
            $id,
        ];
    }

    /**
     * @param object                                $entity       Entity
     * @param \Symfony\Component\Form\FormInterface $form         Form
     * @param object|null                           $parentEntity Parent entity
     * @param bool                                  $partial      Whether to render partial
     *
     * @return string
     */
    private function renderEditTemplate($entity, FormInterface $form, $parentEntity, bool $partial = false): string
    {
        return $this->renderTemplate('edit', [
            'entity'        => $entity,
            'form'          => $form->createView(),
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
        ], $partial);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form         Entity form
     * @param object|null                           $parentEntity Parent entity
     * @param bool                                  $widget       Is widget
     * @param bool                                  $partial      Whether to render partial
     *
     * @return string
     */
    private function renderNewTemplate(FormInterface $form, $parentEntity, bool $widget, bool $partial = false): string
    {
        if ($widget) {
            $partial = true;
        }

        return $this->renderTemplate('new', [
            'form'          => $form->createView(),
            'is_widget'     => $widget,
            'meta'          => $this->meta,
            'parent_entity' => $parentEntity,
        ], $partial);
    }

    /**
     * @param string $viewType       View type
     * @param array  $templateParams Template parameters
     * @param bool   $partial        Whether to render partial
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderResponse(string $viewType, array $templateParams = [], bool $partial = false): Response
    {
        return new Response($this->renderTemplate($viewType, $templateParams, $partial));
    }

    /**
     * @param string $type    Template type
     * @param array  $params  Template parameters
     * @param bool   $partial Whether to render partial
     *
     * @return string
     */
    private function renderTemplate(string $type, array $params = [], bool $partial = false): string
    {
        $template = $this->configuration['view'][$type]['template'];

        if ($partial) {
            if (!empty($template)) {
                return $this->renderView($template, $params);
            }

            $type = sprintf('_%s', $type);
        }

        return $this->renderView(sprintf('@DarvinAdmin/crud/%s.html.twig', $type), $params);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form   Form
     * @param object                                $entity Entity
     *
     * @return string
     */
    private function successRedirect(FormInterface $form, $entity): string
    {
        foreach ($form->all() as $name => $child) {
            if ($child instanceof ClickableInterface && $child->isClicked() && isset(static::SUBMIT_BUTTON_REDIRECTS[$name])) {
                return $this->getAdminRouter()->generate($entity, $this->entityClass, static::SUBMIT_BUTTON_REDIRECTS[$name]);
            }
        }

        return $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouter::TYPE_EDIT);
    }





    /** @return \Darvin\AdminBundle\Form\AdminFormFactory */
    private function getAdminFormFactory()
    {
        return $this->get('darvin_admin.form.factory');
    }

    /** @return \Darvin\AdminBundle\Route\AdminRouter */
    private function getAdminRouter()
    {
        return $this->get('darvin_admin.router');
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

    /** @return \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
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

    /** @return \Darvin\AdminBundle\Form\Handler\NewActionFilterFormHandler */
    private function getNewActionFilterFormHandler()
    {
        return $this->get('darvin_admin.form.handler.new_action_filter');
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

    /** @return \Symfony\Component\Translation\TranslatorInterface */
    private function getTranslator()
    {
        return $this->get('translator');
    }

    /** @return \Darvin\ContentBundle\Translatable\TranslationsInitializer */
    private function getTranslationsInitializer()
    {
        return $this->get('darvin_content.translatable.translations_initializer');
    }

    /** @return \Darvin\Utils\User\UserQueryBuilderFiltererInterface */
    private function getUserQueryBuilderFilterer()
    {
        return $this->get('darvin_utils.user.query_builder_filterer');
    }
}
