<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\ContentBundle\Widget\WidgetInterface;
use Darvin\ContentBundle\Widget\WidgetPoolInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKEditor form type
 */
class CKEditorType extends AbstractType
{
    const CKEDITOR_TYPE_CLASS = __CLASS__;

    const CONFIG_NAME = 'darvin_admin';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Darvin\ContentBundle\Widget\WidgetPoolInterface
     */
    private $widgetPool;

    /**
     * @var string
     */
    private $pluginFilename;

    /**
     * @var string
     */
    private $pluginsPath;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack   $requestStack   Request stack
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface $widgetPool     Widget pool
     * @param string                                           $pluginFilename Plugin filename
     * @param string                                           $pluginsPath    Plugins path
     */
    public function __construct(RequestStack $requestStack, WidgetPoolInterface $widgetPool, $pluginFilename, $pluginsPath)
    {
        $this->requestStack = $requestStack;
        $this->widgetPool = $widgetPool;
        $this->pluginFilename = $pluginFilename;
        $this->pluginsPath = $pluginsPath;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $plugins = [
            'lineutils' => [
                'path'     => $this->pluginsPath.'/lineutils/',
                'filename' => 'plugin.js',
            ],
            'widget' => [
                'path'     => $this->pluginsPath.'/widget/',
                'filename' => 'plugin.js',
            ],
        ];

        $extraPlugins = [
            'lineutils',
            'widget',
        ];

        foreach ($this->widgetPool->getAllWidgets() as $widget) {
            $path = $this->getWidgetPluginPath($widget);

            if (empty($path)) {
                continue;
            }

            $widgetName = $widget->getName();

            $plugins[$widgetName] = [
                'path'     => $path,
                'filename' => $this->getWidgetPluginFilename($widget),
            ];
            $extraPlugins[] = $widgetName;
        }

        // Config
        $config = $view->vars['config'];

        $extraPluginsString = implode(',', $extraPlugins);
        $config['extraPlugins'] = isset($config['extraPlugins']) && !empty($config['extraPlugins'])
            ? $config['extraPlugins'].','.$extraPluginsString
            : $extraPluginsString;

        if (isset($config['toolbar'])) {
            $config['toolbar'] = array_merge($config['toolbar'], [$extraPlugins]);
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!isset($config['language']) && !empty($request)) {
            $config['language'] = $request->getLocale();
        }

        $view->vars['config'] = $config;

        // Plugins
        $view->vars['plugins'] = isset($view->vars['plugins'])
            ? array_merge($view->vars['plugins'], $plugins)
            : $plugins;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('config_name', self::CONFIG_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Ivory\CKEditorBundle\Form\Type\CKEditorType';
    }

    /**
     * @param \Darvin\ContentBundle\Widget\WidgetInterface $widget Widget
     *
     * @return string
     */
    private function getWidgetPluginPath(WidgetInterface $widget)
    {
        $options = $widget->getOptions();

        if (!isset($options['ckeditor_plugin_path'])) {
            return null;
        }

        $path = $options['ckeditor_plugin_path'];

        if ('/' !== substr($path, -1)) {
            $path .= '/';
        }

        return $path;
    }

    /**
     * @param \Darvin\ContentBundle\Widget\WidgetInterface $widget Widget
     *
     * @return string
     */
    private function getWidgetPluginFilename(WidgetInterface $widget)
    {
        $options = $widget->getOptions();

        return isset($options['ckeditor_plugin_filename']) ? $options['ckeditor_plugin_filename'] : $this->pluginFilename;
    }
}
