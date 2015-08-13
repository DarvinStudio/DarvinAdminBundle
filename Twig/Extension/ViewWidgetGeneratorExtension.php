<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 12.08.15
 * Time: 12:34
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPoolProvider;

/**
 * View widget generator Twig extension
 */
class ViewWidgetGeneratorExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPoolProvider
     */
    private $widgetGeneratorPoolProvider;

    /**
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPoolProvider $widgetGeneratorPoolProvider View widget generator pool provider
     */
    public function __construct(WidgetGeneratorPoolProvider $widgetGeneratorPoolProvider)
    {
        $this->widgetGeneratorPoolProvider = $widgetGeneratorPoolProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = array();

        foreach ($this->widgetGeneratorPoolProvider->getPool()->getAll() as $alias => $generator) {
            $functions[] = new \Twig_SimpleFunction('admin_widget_'.$alias, array($generator, 'generate'), array('is_safe' => array('html')));
        }

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_view_widget_generator_extension';
    }
}
