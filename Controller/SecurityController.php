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
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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

        $error = $this->getAuthenticationUtils()->getLastAuthenticationError();
        $form  = $this->getSecurityFormFactory()->createLoginForm([
            'action' => $this->generateUrl('darvin_admin_security_login_check'),
        ]);

        $html = $this->renderView(sprintf('@DarvinAdmin/security/%slogin.html.twig', $request->isXmlHttpRequest() ? '_' : ''), [
            'error' => !empty($error) ? $error->getMessage() : null,
            'form'  => $form->createView(),
        ]);

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($html);
        }

        return new Response($html);
    }

    /**
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils
     */
    private function getAuthenticationUtils(): AuthenticationUtils
    {
        return $this->get('security.authentication_utils');
    }

    /**
     * @return \Darvin\UserBundle\Form\Factory\SecurityFormFactoryInterface
     */
    private function getSecurityFormFactory(): SecurityFormFactoryInterface
    {
        return $this->get('darvin_user.security.form.factory');
    }
}
