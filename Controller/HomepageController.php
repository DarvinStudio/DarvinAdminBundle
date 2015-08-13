<?php
/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 03.08.15
 * Time: 17:13
 * To change this template use File | Settings | File Templates.
 */

namespace Darvin\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Homepage controller
 */
class HomepageController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homepageAction()
    {
        return $this->render('DarvinAdminBundle:Homepage:homepage.html.twig');
    }
}
