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

use Darvin\ContentBundle\Widget\WidgetPoolInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKEditor form type
 */
class CKEditorType extends AbstractType
{
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
     * @var string
     */
    private $webDir;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack   $requestStack   Request stack
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface $widgetPool     Widget pool
     * @param string                                           $pluginFilename Plugin filename
     * @param string                                           $pluginsPath    Plugins path
     * @param string                                           $webDir         Web directory
     */
    public function __construct(
        RequestStack $requestStack,
        WidgetPoolInterface $widgetPool,
        $pluginFilename,
        $pluginsPath,
        $webDir
    ) {
        $this->requestStack = $requestStack;
        $this->widgetPool = $widgetPool;
        $this->pluginFilename = $pluginFilename;
        $this->pluginsPath = $pluginsPath;
        $this->webDir = $webDir;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $plugins = array(
            'lineutils' => array(
                'path'     => $this->pluginsPath.'/lineutils/',
                'filename' => 'plugin.js',
            ),
            'widget' => array(
                'path'     => $this->pluginsPath.'/widget/',
                'filename' => 'plugin.js',
            ),
        );

        $extraPlugins = array(
            'lineutils',
            'widget',
        );

        foreach ($this->widgetPool->getAllWidgets() as $widget) {
            $widgetName = $widget->getName();

            $path = sprintf('%s/%s/', $this->pluginsPath, $widgetName);

            if (!file_exists($this->webDir.'/'.$path)) {
                continue;
            }

            $plugins[$widgetName] = array(
                'path'     => $path,
                'filename' => $this->pluginFilename,
            );
            $extraPlugins[] = $widgetName;
        }

        $config = array(
            'extraPlugins' => implode(',', $extraPlugins),
        );

        $request = $this->requestStack->getCurrentRequest();

        if (!empty($request)) {
            $config['language'] = $request->getLocale();
        }

        $resolver->setDefaults(array(
            'config'  => $config,
            'plugins' => $plugins,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_ckeditor';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'ckeditor';
    }
}
