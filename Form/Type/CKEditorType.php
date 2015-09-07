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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKEditor form type
 */
class CKEditorType extends AbstractType
{
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
    private $pluginsDir;

    /**
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface $widgetPool     Widget pool
     * @param string                                           $pluginFilename Plugin filename
     * @param string                                           $pluginsDir     Plugins directory
     */
    public function __construct(WidgetPoolInterface $widgetPool, $pluginFilename, $pluginsDir)
    {
        $this->widgetPool = $widgetPool;
        $this->pluginFilename = $pluginFilename;
        $this->pluginsDir = $pluginsDir;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $plugins = $extraPlugins = array();

        foreach ($this->widgetPool->getAll() as $widget) {
            $widgetName = $widget->getName();

            $path = sprintf('%s/%s/', $this->pluginsDir, $widgetName);

            if (!file_exists($path.$this->pluginFilename)) {
                continue;
            }

            $plugins[$widgetName] = array(
                'path'     => $path,
                'filename' => $this->pluginFilename,
            );
            $extraPlugins[] = $widgetName;
        }

        $resolver->setDefaults(array(
            'config'  => array(
                'extraPlugins' => implode(',', $extraPlugins),
            ),
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
