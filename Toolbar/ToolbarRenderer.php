<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darvin\AdminBundle\Toolbar;

use Darvin\AdminBundle\Security\User\Roles;
use Darvin\AdminBundle\View\Widget\WidgetInterface;
use Darvin\ContentBundle\Entity\ContentReference;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\Utils\Homepage\HomepageProviderInterface;
use Darvin\Utils\Homepage\HomepageRouterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Twig\Environment;

/**
 * Toolbar renderer
 */
class ToolbarRenderer implements ToolbarRendererInterface
{
    private const ROUTE            = 'darvin_content_show';
    private const ROUTE_PARAM_SLUG = 'slug';

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetInterface
     */
    private $editLinkWidget;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Darvin\Utils\Homepage\HomepageProviderInterface
     */
    private $homepageProvider;

    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\AdminBundle\View\Widget\WidgetInterface                              $editLinkWidget       Edit link view widget
     * @param \Doctrine\ORM\EntityManagerInterface                                         $em                   Entity manager
     * @param \Darvin\Utils\Homepage\HomepageProviderInterface                             $homepageProvider     Homepage provider
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface                               $homepageRouter       Homepage router
     * @param \Symfony\Component\HttpFoundation\RequestStack                               $requestStack         Request stack
     * @param \Twig\Environment                                                            $twig                 Twig
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        WidgetInterface $editLinkWidget,
        EntityManagerInterface $em,
        HomepageProviderInterface $homepageProvider,
        HomepageRouterInterface $homepageRouter,
        RequestStack $requestStack,
        Environment $twig
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->editLinkWidget = $editLinkWidget;
        $this->em = $em;
        $this->homepageProvider = $homepageProvider;
        $this->homepageRouter = $homepageRouter;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderToolbar(): ?string
    {
        try {
            if (!$this->authorizationChecker->isGranted(Roles::ROLE_ADMIN)) {
                return null;
            }
        } catch (AuthenticationCredentialsNotFoundException $ex) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $entity = $this->findEntity($request);

        if (null === $entity) {
            return null;
        }

        $editLink = $this->editLinkWidget->getContent($entity, [
            'style' => 'toolbar',
        ]);

        return $this->twig->render('@DarvinAdmin/toolbar.html.twig', [
            'edit_link' => $editLink,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return object|null
     */
    private function findEntity(Request $request): ?object
    {
        if (self::ROUTE !== $request->attributes->get('_route')) {
            if ($request->getBaseUrl().$request->getPathInfo() === $this->homepageRouter->generate()) {
                return $this->homepageProvider->getHomepage();
            }

            return null;
        }
        if (!$request->attributes->has('_route_params')) {
            return null;
        }

        $routeParams = $request->attributes->get('_route_params');

        if (!is_array($routeParams) || !isset($routeParams[self::ROUTE_PARAM_SLUG])) {
            return null;
        }
        if (class_exists(SlugMapItem::class)) {
            $slug = $this->findSlugMapItem($routeParams[self::ROUTE_PARAM_SLUG]);

            if (null === $slug) {
                return null;
            }

            return $this->em->getRepository($slug->getObjectClass())->find($slug->getObjectId());
        }

        $contentReference = $this->findContentReference($routeParams[self::ROUTE_PARAM_SLUG]);

        if (null === $contentReference) {
            return null;
        }

        return $this->em->getRepository($contentReference->getObjectClass())->find($contentReference->getObjectId());
    }

    /**
     * @param string $slug Slug
     *
     * @return \Darvin\ContentBundle\Entity\ContentReference|null
     */
    private function findContentReference(string $slug): ?ContentReference
    {
        return $this->em->getRepository(ContentReference::class)->findOneBy([
            'slug' => $slug,
        ]);
    }

    /**
     * @param string $slug Slug
     *
     * @return \Darvin\ContentBundle\Entity\SlugMapItem|null
     */
    private function findSlugMapItem(string $slug): ?SlugMapItem
    {
        return $this->em->getRepository(SlugMapItem::class)->findOneBy([
            'slug' => $slug,
        ]);
    }
}
