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

use Darvin\AdminBundle\Dashboard\DashboardInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Homepage controller
 */
class HomepageController
{
    /**
     * @var \Darvin\AdminBundle\Dashboard\DashboardInterface
     */
    private $dashboard;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @param \Darvin\AdminBundle\Dashboard\DashboardInterface $dashboard Dashboard
     * @param \Twig\Environment                                $twig      Twig
     */
    public function __construct(DashboardInterface $dashboard, Environment $twig)
    {
        $this->dashboard = $dashboard;
        $this->twig = $twig;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        return new Response($this->twig->render('@DarvinAdmin/homepage/index.html.twig', [
            'widgets' => $this->dashboard->getWidgets(),
        ]));
    }
}
