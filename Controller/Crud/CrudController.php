<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Controller\Crud\Action\ActionConfig;
use Darvin\AdminBundle\Controller\Crud\Action\ActionInterface;
use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CopiedEvent;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\DeletedEvent;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Form\FormHandler;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer;
use Darvin\AdminBundle\View\Widget\Widget\DeleteFormWidget;
use Darvin\AdminBundle\View\Widget\WidgetPool;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * CRUD controller
 */
class CrudController extends Controller
{
    private const SUBMIT_BUTTON_REDIRECTS = [
        AdminFormFactory::SUBMIT_EDIT  => AdminRouterInterface::TYPE_EDIT,
        AdminFormFactory::SUBMIT_INDEX => AdminRouterInterface::TYPE_INDEX,
        AdminFormFactory::SUBMIT_NEW   => AdminRouterInterface::TYPE_NEW,
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
     * @var \Darvin\AdminBundle\Controller\Crud\Action\ActionInterface[]
     */
    private $actions;

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     * @param string                                                     $entityClass     Entity class
     */
    public function __construct(AdminMetadataManagerInterface $metadataManager, string $entityClass)
    {
        $this->meta = $metadataManager->getMetadata($entityClass);
        $this->configuration = $this->meta->getConfiguration();
        $this->entityClass = $entityClass;

        $this->actions = [];
    }

    /**
     * @param \Darvin\AdminBundle\Controller\Crud\Action\ActionInterface $action Action
     */
    public function addAction(ActionInterface $action): void
    {
        $this->actions[$action->getName()] = $action;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param bool                                      $widget  Is widget
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, bool $widget = false): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
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
            $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouterInterface::TYPE_INDEX)
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
            $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouterInterface::TYPE_EDIT),
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
    public function showAction(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
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

        $form        = $this->getAdminFormFactory()->createDeleteForm($entity, $this->entityClass)->handleRequest($request);
        $redirectUrl = $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouterInterface::TYPE_INDEX);
        $referer     = $request->headers->get('referer');

        if (!empty($referer) && parse_url($referer, PHP_URL_PATH) === $redirectUrl) {
            $redirectUrl = $referer;
        }
        if (!$form->isValid()) {
            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));

            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($this->getViewWidgetPool()->getWidget(DeleteFormWidget::ALIAS)->getContent($entity), false, $message);
            }

            $this->getFlashNotifier()->error($message);

            return $this->redirect($redirectUrl);
        }

        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();

        $this->getEventDispatcher()->dispatch(CrudEvents::DELETED, new DeletedEvent($this->meta, $this->getUser(), $entity));

        $message = sprintf('%saction.delete.success', $this->meta->getBaseTranslationPrefix());

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

            return $this->redirect($this->getAdminRouter()->generate(reset($entities), $this->entityClass, AdminRouterInterface::TYPE_INDEX));
        }

        $url = $request->headers->get(
            'referer',
            $this->getAdminRouter()->generate(reset($entities), $this->entityClass, AdminRouterInterface::TYPE_INDEX)
        );

        return $this->redirect($url);
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

        foreach (self::SUBMIT_BUTTON_REDIRECTS as $button => $routeType) {
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
            if ($child instanceof ClickableInterface && $child->isClicked() && isset(self::SUBMIT_BUTTON_REDIRECTS[$name])) {
                return $this->getAdminRouter()->generate($entity, $this->entityClass, self::SUBMIT_BUTTON_REDIRECTS[$name]);
            }
        }

        return $this->getAdminRouter()->generate($entity, $this->entityClass, AdminRouterInterface::TYPE_EDIT);
    }

    /**
     * @param string $name Name
     * @param array  $args Arguments
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    private function action(string $name, array $args): Response
    {
        if (!isset($this->actions[$name])) {
            throw new \InvalidArgumentException(sprintf('CRUD action "%s" does not exist.', $name));
        }

        $action = $this->actions[$name];
        $action->configure(new ActionConfig($this->entityClass));

        return $action->{$action->getRunMethod()}(...$args);
    }





    /** @return \Darvin\AdminBundle\Form\AdminFormFactory */
    private function getAdminFormFactory(): AdminFormFactory
    {
        return $this->get('darvin_admin.form.factory');
    }

    /** @return \Darvin\AdminBundle\Route\AdminRouterInterface */
    private function getAdminRouter(): AdminRouterInterface
    {
        return $this->get('darvin_admin.router');
    }

    /** @return \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer */
    private function getEntitiesToIndexViewTransformer(): EntitiesToIndexViewTransformer
    {
        return $this->get('darvin_admin.view.entity_transformer.index');
    }

    /** @return \Doctrine\ORM\EntityManager */
    private function getEntityManager(): EntityManager
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /** @return \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->get('event_dispatcher');
    }

    /** @return \Darvin\Utils\Flash\FlashNotifierInterface */
    private function getFlashNotifier(): FlashNotifierInterface
    {
        return $this->get('darvin_utils.flash.notifier');
    }

    /** @return \Darvin\AdminBundle\Form\FormHandler */
    private function getFormHandler(): FormHandler
    {
        return $this->get('darvin_admin.form.handler');
    }

    /** @return \Symfony\Component\Translation\TranslatorInterface */
    private function getTranslator(): TranslatorInterface
    {
        return $this->get('translator');
    }

    /** @return \Darvin\AdminBundle\View\Widget\WidgetPool */
    private function getViewWidgetPool(): WidgetPool
    {
        return $this->get('darvin_admin.view.widget.pool');
    }
}
