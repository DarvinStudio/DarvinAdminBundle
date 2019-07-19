<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Form\AdminFormFactoryInterface;
use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Metadata\Metadata;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\UserBundle\User\UserManagerInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\Routing\RouteManagerInterface;
use Darvin\Utils\Strings\StringsUtil;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

/**
 * CRUD controller action abstract implementation
 */
abstract class AbstractAction
{
    private const SUBMIT_BUTTON_REDIRECTS = [
        [
            AdminFormFactoryInterface::SUBMIT_EDIT  => AdminRouterInterface::TYPE_EDIT,
            AdminFormFactoryInterface::SUBMIT_INDEX => AdminRouterInterface::TYPE_INDEX,
            AdminFormFactoryInterface::SUBMIT_NEW   => AdminRouterInterface::TYPE_NEW,
        ],
        [
            AdminFormFactoryInterface::SUBMIT_EDIT  => AdminRouterInterface::TYPE_EDIT,
            AdminFormFactoryInterface::SUBMIT_INDEX => AdminRouterInterface::TYPE_INDEX,
        ],
    ];

    /**
     * @var \Darvin\AdminBundle\Form\AdminFormFactoryInterface
     */
    protected $adminFormFactory;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    protected $adminMetadataManager;

    /**
     * @var \Darvin\AdminBundle\Route\AdminRouterInterface
     */
    protected $adminRouter;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    protected $flashNotifier;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    protected $routeManager;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var \Darvin\UserBundle\User\UserManagerInterface
     */
    protected $userManager;

    /**
     * @var \Darvin\AdminBundle\Controller\Crud\ActionConfig|null
     */
    private $actionConfig;

    /**
     * @var array|null
     */
    private $config = null;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata|null
     */
    private $meta = null;

    /**
     * @var string|null
     */
    private $entityClass = null;

    /**
     * @var string|null
     */
    private $name = null;

    /**
     * @param \Darvin\AdminBundle\Form\AdminFormFactoryInterface $adminFormFactory Admin form factory
     */
    public function setAdminFormFactory(AdminFormFactoryInterface $adminFormFactory): void
    {
        $this->adminFormFactory = $adminFormFactory;
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $adminMetadataManager Admin metadata manager
     */
    public function setAdminMetadataManager(AdminMetadataManagerInterface $adminMetadataManager): void
    {
        $this->adminMetadataManager = $adminMetadataManager;
    }

    /**
     * @param \Darvin\AdminBundle\Route\AdminRouterInterface $adminRouter Admin router
     */
    public function setAdminRouter(AdminRouterInterface $adminRouter): void
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Darvin\Utils\Flash\FlashNotifierInterface $flashNotifier Flash notifier
     */
    public function setFlashNotifier(FlashNotifierInterface $flashNotifier): void
    {
        $this->flashNotifier = $flashNotifier;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param \Darvin\Utils\Routing\RouteManagerInterface $routeManager Route manager
     */
    public function setRouteManager(RouteManagerInterface $routeManager): void
    {
        $this->routeManager = $routeManager;
    }

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating Templating
     */
    public function setTemplating(EngineInterface $templating): void
    {
        $this->templating = $templating;
    }

    /**
     * @param \Darvin\UserBundle\User\UserManagerInterface $userManager User manager
     */
    public function setUserManager(UserManagerInterface $userManager): void
    {
        $this->userManager = $userManager;
    }

    /**
     * @param \Darvin\AdminBundle\Controller\Crud\ActionConfig|null $actionConfig Action configuration
     */
    public function configure(?ActionConfig $actionConfig): void
    {
        $this->actionConfig = $actionConfig;
    }

    /**
     * @param string $permission Permission
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function checkPermission(string $permission): void
    {
        if (!$this->authorizationChecker->isGranted($permission, $this->getEntityClass())) {
            throw new AccessDeniedException(
                sprintf('You do not have "%s" permission on "%s" class objects.', $permission, $this->getEntityClass())
            );
        }
    }

    /**
     * @param mixed       $id    Entity ID
     * @param string|null $class Entity class
     *
     * @return object
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function findEntity($id, ?string $class = null)
    {
        if (empty($class)) {
            $class = $this->getEntityClass();
        }

        $entity = $this->em->find($class, $id);

        if (empty($entity)) {
            throw new NotFoundHttpException(sprintf('Unable to find entity "%s" by ID "%s".', $class, $id));
        }

        return $entity;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getParentEntityDefinition(Request $request): array
    {
        if (!$this->getMeta()->hasParent()) {
            return array_fill(0, 4, null);
        }

        $associationParam = $this->getMeta()->getParent()->getAssociationParameterName();

        $id = $request->query->get($associationParam);

        if (empty($id)) {
            throw new NotFoundHttpException(sprintf('Value of query parameter "%s" must be provided.', $associationParam));
        }

        return [
            $this->findEntity($id, $this->getMeta()->getParent()->getMetadata()->getEntityClass()),
            $this->getMeta()->getParent()->getAssociation(),
            $associationParam,
            $id,
        ];
    }

    /**
     * @return array
     */
    protected function getSubmitButtons(): array
    {
        $buttons = [];

        foreach (self::SUBMIT_BUTTON_REDIRECTS[$this->getConfig()['single_instance']] as $button => $routeType) {
            if ($this->adminRouter->exists($this->getEntityClass(), $routeType)) {
                $buttons[] = $button;
            }
        }

        return $buttons;
    }

    /**
     * @param array $params  Template parameters
     * @param bool  $partial Whether to render partial
     *
     * @return string
     */
    protected function renderTemplate(array $params = [], bool $partial = false): string
    {
        if (null !== $this->actionConfig && null !== $this->actionConfig->getTemplate()) {
            return $this->templating->render($this->actionConfig->getTemplate(), $params);
        }

        $config = $this->getConfig();
        $type   = $this->getName();

        $template = $config['view'][$type]['template'];

        if ($partial) {
            if (!empty($template)) {
                return $this->templating->render($template, $params);
            }

            $type = sprintf('_%s', $type);
        }

        return $this->templating->render(sprintf('@DarvinAdmin/crud/%s.html.twig', $type), $params);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form   Form
     * @param object                                $entity Entity
     *
     * @return string
     */
    protected function successRedirect(FormInterface $form, $entity): string
    {
        $redirects = self::SUBMIT_BUTTON_REDIRECTS[$this->getConfig()['single_instance']];

        foreach ($form->all() as $name => $child) {
            if ($child instanceof ClickableInterface
                && $child->isClicked()
                && isset($redirects[$name])
                && $this->adminRouter->exists($this->getEntityClass(), $redirects[$name])
            ) {
                return $this->adminRouter->generate($entity, $this->getEntityClass(), $redirects[$name]);
            }
        }
        if ($this->adminRouter->exists($this->getEntityClass(), AdminRouterInterface::TYPE_EDIT)) {
            return $this->adminRouter->generate($entity, $this->getEntityClass(), AdminRouterInterface::TYPE_EDIT);
        }

        return '';
    }

    /**
     * @return array
     */
    protected function getConfig(): array
    {
        if (null === $this->config) {
            $this->config = $this->getMeta()->getConfiguration();
        }

        return $this->config;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    protected function getMeta(): Metadata
    {
        if (null === $this->meta) {
            $this->meta = $this->adminMetadataManager->getMetadata($this->getEntityClass());
        }

        return $this->meta;
    }

    /**
     * @return string
     */
    protected function getEntityClass(): string
    {
        if (null !== $this->actionConfig && null !== $this->actionConfig->getEntityClass()) {
            return $this->actionConfig->getEntityClass();
        }
        if (null === $this->entityClass) {
            $this->entityClass = $this->routeManager->getOption(
                $this->requestStack->getCurrentRequest()->attributes->get('_route'),
                AdminRouterInterface::OPTION_ENTITY_CLASS
            );
        }

        return $this->entityClass;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        if (null === $this->name) {
            $this->name = StringsUtil::toUnderscore(preg_replace('/^.*\\\|Action$/', '', get_class($this)));
        }

        return $this->name;
    }
}
