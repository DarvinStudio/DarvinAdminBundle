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
use Darvin\ContentBundle\Entity\SlugMapItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @param \Symfony\Component\HttpFoundation\RequestStack                               $requestStack         Request stack
     * @param \Twig\Environment                                                            $twig                 Twig
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        WidgetInterface $editLinkWidget,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        Environment $twig
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->editLinkWidget = $editLinkWidget;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
    }

    /**
     * {@inheritDoc}
     */
    public function renderToolbar(): ?string
    {
        if (!$this->authorizationChecker->isGranted(Roles::ROLE_ADMIN)) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request
            || self::ROUTE !== $request->attributes->get('_route')
            || !$request->attributes->has('_route_params')
        ) {
            return null;
        }

        $routeParams = $request->attributes->get('_route_params');

        if (!is_array($routeParams) || !isset($routeParams[self::ROUTE_PARAM_SLUG])) {
            return null;
        }

        $slug = $this->findSlugMapItem($routeParams[self::ROUTE_PARAM_SLUG]);

        if (null === $slug) {
            return null;
        }

        $entity = $this->em->getRepository($slug->getObjectClass())->find($slug->getObjectId());

        if (null === $entity) {
            return null;
        }

        $editLink = $this->editLinkWidget->getContent($entity);

        return $this->twig->render('@DarvinAdmin/toolbar.html.twig', [
            'edit_link' => $editLink,
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
