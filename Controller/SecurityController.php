<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Security controller
 */
class SecurityController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            return $this->redirectToRoute('darvin_admin_homepage');
        }

        $authenticationUtils = $this->getAuthenticationUtils();

        $form = $this->getLoginFormFactory()->createLoginForm('darvin_admin_security_login_check');

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('DarvinAdminBundle:Security:login.html.twig', [
            'error' => !empty($error) ? $error->getMessage() : null,
            'form'  => $form->createView(),
        ]
        );
    }

    /**
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils
     */
    private function getAuthenticationUtils()
    {
        return $this->get('security.authentication_utils');
    }

    /**
     * @return \Darvin\UserBundle\Form\Factory\Security\LoginFormFactoryInterface
     */
    private function getLoginFormFactory()
    {
        return $this->get('darvin_user.security.form.factory.login');
    }
}
