<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud\Action;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\UserBundle\User\UserManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

/**
 * CRUD controller action abstract implementation
 */
abstract class AbstractAction implements ActionInterface
{
    private const RUN_METHOD = 'run';

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    protected $adminMetadataManager;

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
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var \Darvin\UserBundle\User\UserManagerInterface
     */
    protected $userManager;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    protected $meta;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $adminMetadataManager Admin metadata manager
     */
    public function setAdminMetadataManager(AdminMetadataManagerInterface $adminMetadataManager): void
    {
        $this->adminMetadataManager = $adminMetadataManager;
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
     * {@inheritdoc}
     */
    public function configure(ActionConfig $actionConfig): void
    {
        $this->entityClass = $actionConfig->getEntityClass();

        $this->meta = $this->adminMetadataManager->getMetadata($this->entityClass);

        $this->config = $this->meta->getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function getRunMethod(): string
    {
        return self::RUN_METHOD;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        $parts = explode('\\', get_class($this));

        return lcfirst(array_pop($parts));
    }

    /**
     * @param string $permission Permission
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function checkPermission(string $permission): void
    {
        if (!$this->authorizationChecker->isGranted($permission, $this->entityClass)) {
            throw new AccessDeniedException(
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
    protected function findEntity($id, ?string $class = null)
    {
        if (empty($class)) {
            $class = $this->entityClass;
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
        if (!$this->meta->hasParent()) {
            return array_fill(0, 4, null);
        }

        $associationParam = $this->meta->getParent()->getAssociationParameterName();

        $id = $request->query->get($associationParam);

        if (empty($id)) {
            throw new NotFoundHttpException(sprintf('Value of query parameter "%s" must be provided.', $associationParam));
        }

        return [
            $this->findEntity($id, $this->meta->getParent()->getMetadata()->getEntityClass()),
            $this->meta->getParent()->getAssociation(),
            $associationParam,
            $id,
        ];
    }

    /**
     * @param string $viewType       View type
     * @param array  $templateParams Template parameters
     * @param bool   $partial        Whether to render partial
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderResponse(string $viewType, array $templateParams = [], bool $partial = false): Response
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
    protected function renderTemplate(string $type, array $params = [], bool $partial = false): string
    {
        $template = $this->config['view'][$type]['template'];

        if ($partial) {
            if (!empty($template)) {
                return $this->templating->render($template, $params);
            }

            $type = sprintf('_%s', $type);
        }

        return $this->templating->render(sprintf('@DarvinAdmin/crud/%s.html.twig', $type), $params);
    }
}
