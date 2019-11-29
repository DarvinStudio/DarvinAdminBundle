<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Security;

use Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface;
use Darvin\UserBundle\Form\Renderer\SecurityFormRendererInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Security login controller
 */
class LoginController
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Darvin\UserBundle\Form\Renderer\SecurityFormRendererInterface
     */
    private $formRenderer;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface                 $formFactory          Security form factory
     * @param \Darvin\UserBundle\Form\Renderer\SecurityFormRendererInterface               $formRenderer         Security form renderer
     * @param \Symfony\Component\Routing\RouterInterface                                   $router               Router
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        SecurityFormFactoryInterface $formFactory,
        SecurityFormRendererInterface $formRenderer,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->formRenderer = $formRenderer;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        if ($this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            return new RedirectResponse($this->router->generate('darvin_admin_homepage'));
        }

        $html = $this->formRenderer->renderLoginForm(
            $this->formFactory->createLoginForm([
                'action' => $this->router->generate('darvin_admin_security_login_check'),
            ]),
            $request->isXmlHttpRequest(),
            sprintf('@DarvinAdmin/security/%slogin.html.twig', $request->isXmlHttpRequest() ? '_' : '')
        );

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($html);
        }

        return new Response($html);
    }
}
