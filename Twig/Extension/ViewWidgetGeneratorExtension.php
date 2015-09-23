<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        foreach ($this->widgetGeneratorPoolProvider->getPool()->getAllWidgetGenerators() as $alias => $generator) {
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
