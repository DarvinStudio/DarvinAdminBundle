<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\CKEditor\AbstractCKEditorWidget;
use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Darvin\ContentBundle\Widget\WidgetPoolInterface;
use Darvin\Utils\Locale\LocaleProviderInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * CKEditor form type
 */
class CKEditorType extends AbstractType
{
    const CONFIG_NAME = 'darvin_admin';

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

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
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                    $localeProvider      Locale provider
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface     $propertyAccessor    Property accessor
     * @param \Symfony\Component\Routing\RouterInterface                      $router              Router
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface                $widgetPool          Widget pool
     * @param string                                                          $pluginFilename      Plugin filename
     * @param string                                                          $pluginsPath         Plugins path
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        PropertyAccessorInterface $propertyAccessor,
        RouterInterface $router,
        TranslatableManagerInterface $translatableManager,
        WidgetPoolInterface $widgetPool,
        $pluginFilename,
        $pluginsPath
    ) {
        $this->localeProvider = $localeProvider;
        $this->propertyAccessor = $propertyAccessor;
        $this->router = $router;
        $this->translatableManager = $translatableManager;
        $this->widgetPool = $widgetPool;
        $this->pluginFilename = $pluginFilename;
        $this->pluginsPath = $pluginsPath;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->removeExtraPluginsFromBlacklist($view);

        $view->vars['locale'] = $this->getLocale($form);

        if (!isset($view->vars['config']['language'])) {
            $view->vars['config']['language'] = $this->localeProvider->getCurrentLocale();
        }
        if (!$options['enable_widgets']) {
            return;
        }

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
            if (!$widget instanceof AbstractCKEditorWidget) {
                continue;
            }

            $widgetName = $widget->getName();

            $plugins[$widgetName] = [
                'path'     => $this->router->generate('darvin_admin_ckeditor_plugin_path', [
                    'widgetName' => $widgetName,
                ]),
                'filename' => 'plugin.js',
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
        $resolver
            ->setDefaults([
                'config_name'    => self::CONFIG_NAME,
                'enable_widgets' => false,
            ])
            ->setAllowedTypes('config_name', 'string')
            ->setAllowedTypes('enable_widgets', 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return \FOS\CKEditorBundle\Form\Type\CKEditorType::class;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form Form
     *
     * @return string|null
     */
    private function getLocale(FormInterface $form)
    {
        if (null === $form->getParent()) {
            return null;
        }

        $entity = $form->getParent()->getData();

        if (!is_object($entity)) {
            return null;
        }

        $class = ClassUtils::getClass($entity);

        if (!$this->translatableManager->isTranslation($class)) {
            return null;
        }

        return $this->propertyAccessor->getValue($entity, $this->translatableManager->getTranslationLocaleProperty());
    }

    /**
     * @param \Symfony\Component\Form\FormView $view Form view
     */
    private function removeExtraPluginsFromBlacklist(FormView $view)
    {
        $config = $view->vars['config'];

        if (!isset($config['extraPlugins']) || !isset($config['removePlugins'])) {
            return;
        }

        $extraPlugins  = array_map('trim', explode(',', $config['extraPlugins']));
        $removePlugins = array_map('trim', explode(',', $config['removePlugins']));

        $view->vars['config']['removePlugins'] = implode(',', array_diff($removePlugins, $extraPlugins));
    }
}
