<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 12.08.15
 * Time: 12:51
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * View widget generator pool provider
 */
class WidgetGeneratorPoolProvider
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
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    public function getPool()
    {
        return $this->container->get('darvin_admin.view.widget_generator.pool');
    }
}
