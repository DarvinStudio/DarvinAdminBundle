<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\UserBundle\Form\Factory\Security\LoginFormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Security controller
 */
class SecurityController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(): Response
    {
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            return $this->redirectToRoute('darvin_admin_homepage');
        }

        $authenticationUtils = $this->getAuthenticationUtils();

        $form = $this->getLoginFormFactory()->createLoginForm('darvin_admin_security_login_check');

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('@DarvinAdmin/security/login.html.twig', [
            'error' => !empty($error) ? $error->getMessage() : null,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils
     */
    private function getAuthenticationUtils(): AuthenticationUtils
    {
        return $this->get('security.authentication_utils');
    }

    /**
     * @return \Darvin\UserBundle\Form\Factory\Security\LoginFormFactoryInterface
     */
    private function getLoginFormFactory(): LoginFormFactoryInterface
    {
        return $this->get('darvin_user.security.form.factory.login');
    }
}
