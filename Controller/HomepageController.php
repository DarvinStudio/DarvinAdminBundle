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
        return $this->render('DarvinAdminBundle:Homepage:homepage.html.twig', [
            'dashboard' => $this->getDashboard(),
        ]
        );
    }

    /**
     * @return \Darvin\AdminBundle\Dashboard\DashboardInterface
     */
    private function getDashboard()
    {
        return $this->get('darvin_admin.dashboard.dashboard');
    }
}
