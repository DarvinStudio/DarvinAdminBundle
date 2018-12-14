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
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Form\AdminFormFactory;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * CRUD controller
 */
class CrudController extends Controller
{
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copyAction(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $id): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function batchDeleteAction(Request $request): Response
    {
        return $this->action(__FUNCTION__, func_get_args());
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

    /** @return \Symfony\Component\Translation\TranslatorInterface */
    private function getTranslator(): TranslatorInterface
    {
        return $this->get('translator');
    }
}
