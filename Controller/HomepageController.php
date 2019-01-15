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

use Darvin\AdminBundle\Dashboard\DashboardInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Homepage controller
 */
class HomepageController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('@DarvinAdmin/homepage/index.html.twig', [
            'widgets' => $this->getDashboard()->getWidgets(),
        ]);
    }

    /**
     * @return \Darvin\AdminBundle\Dashboard\DashboardInterface
     */
    private function getDashboard(): DashboardInterface
    {
        return $this->get('darvin_admin.dashboard');
    }
}
