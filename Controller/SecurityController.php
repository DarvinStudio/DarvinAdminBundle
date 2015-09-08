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

use Darvin\AdminBundle\Form\Type\Security\LoginType;
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

        $form = $this->createForm(
            new LoginType($this->container->getParameter('secret')),
            array(
                '_remember_me' => true,
                '_username'    => $authenticationUtils->getLastUsername(),
            ),
            array(
                'action' => $this->generateUrl('darvin_admin_security_login_check'),
            )
        );

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('DarvinAdminBundle:Security:login.html.twig', array(
            'error' => !empty($error) ? $error->getMessage() : null,
            'form'  => $form->createView(),
        ));
    }

    /**
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils
     */
    private function getAuthenticationUtils()
    {
        return $this->get('security.authentication_utils');
    }
}
