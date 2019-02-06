<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface;
use Darvin\UserBundle\Form\Renderer\SecurityFormRendererInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Security controller
 */
class SecurityController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request): Response
    {
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            return $this->redirectToRoute('darvin_admin_homepage');
        }

        $html = $this->getSecurityFormRenderer()->renderLoginForm(
            $this->getSecurityFormFactory()->createLoginForm([
                'action' => $this->generateUrl('darvin_admin_security_login_check'),
            ]),
            $request->isXmlHttpRequest(),
            sprintf('@DarvinAdmin/security/%slogin.html.twig', $request->isXmlHttpRequest() ? '_' : '')
        );

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($html);
        }

        return new Response($html);
    }

    /**
     * @return \Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface
     */
    private function getSecurityFormFactory(): SecurityFormFactoryInterface
    {
        return $this->get('darvin_user.security.form.factory');
    }

    /**
     * @return \Darvin\UserBundle\Form\Renderer\SecurityFormRendererInterface
     */
    private function getSecurityFormRenderer(): SecurityFormRendererInterface
    {
        return $this->get('darvin_user.security.form.renderer');
    }
}
