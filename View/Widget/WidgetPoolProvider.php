<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * View widget pool provider
 */
class WidgetPoolProvider
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    public function getWidgetPool()
    {
        return $this->container->get('darvin_admin.view.widget.pool');
    }
}
