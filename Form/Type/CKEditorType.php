<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
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
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Symfony\Component\Asset\Packages;
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
    private const CONFIG_NAME = 'darvin_admin';

    /**
     * @var \Symfony\Component\Asset\Packages
     */
    private $assetPackages;

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
     * @var bool
     */
    private $applyContentsCss;

    /**
     * @var string
     */
    private $contentsCssDir;

    /**
     * @var string
     */
    private $pluginFilename;

    /**
     * @var string
     */
    private $pluginsPath;

    /**
     * @param \Symfony\Component\Asset\Packages                               $assetPackages       Asset packages
     * @param \Darvin\Utils\Locale\LocaleProviderInterface                    $localeProvider      Locale provider
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface     $propertyAccessor    Property accessor
     * @param \Symfony\Component\Routing\RouterInterface                      $router              Router
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     * @param \Darvin\ContentBundle\Widget\WidgetPoolInterface                $widgetPool          Widget pool
     * @param bool                                                            $applyContentsCss    Whether to apply contents CSS
     * @param string                                                          $contentsCssDir      Contents CSS directory
     * @param string                                                          $pluginFilename      Plugin filename
     * @param string                                                          $pluginsPath         Plugins path
     */
    public function __construct(
        Packages $assetPackages,
        LocaleProviderInterface $localeProvider,
        PropertyAccessorInterface $propertyAccessor,
        RouterInterface $router,
        TranslatableManagerInterface $translatableManager,
        WidgetPoolInterface $widgetPool,
        bool $applyContentsCss,
        string $contentsCssDir,
        string $pluginFilename,
        string $pluginsPath
    ) {
        $this->assetPackages = $assetPackages;
        $this->localeProvider = $localeProvider;
        $this->propertyAccessor = $propertyAccessor;
        $this->router = $router;
        $this->translatableManager = $translatableManager;
        $this->widgetPool = $widgetPool;
        $this->applyContentsCss = $applyContentsCss;
        $this->contentsCssDir = $contentsCssDir;
        $this->pluginFilename = $pluginFilename;
        $this->pluginsPath = $pluginsPath;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($this->applyContentsCss) {
            $this->applyContentsCss($view, $options);
        }
        
        $this->removeExtraPluginsFromBlacklist($view);

        $view->vars['locale'] = $this->getLocale($form);

        if (!isset($view->vars['config']['extraPlugins'])) {
            $view->vars['config']['extraPlugins'] = '';
        }
        if (!isset($view->vars['config']['language'])) {
            $view->vars['config']['language'] = $this->localeProvider->getCurrentLocale();
        }
        if (!isset($view->vars['config']['toolbar'])) {
            $view->vars['config']['toolbar'] = [];
        }
        if ($options['enable_widgets']) {
            $extraPlugins = [];

            foreach ($this->widgetPool->getAllWidgets() as $widget) {
                if ($widget instanceof AbstractCKEditorWidget) {
                    $extraPlugins[] = $widget->getName();
                }
            }
            if (!empty($extraPlugins)) {
                $extraPluginList = implode(',', $extraPlugins);

                $view->vars['plugins'][$extraPluginList] = [
                    'path'     => $this->router->generate('darvin_admin_ckeditor_plugin_path'),
                    'filename' => 'plugin.js',
                ];

                if ('' !== $view->vars['config']['extraPlugins']) {
                    $view->vars['config']['extraPlugins'] .= ',';
                }

                $view->vars['config']['extraPlugins'] .= $extraPluginList;

                $view->vars['config']['toolbar'][] = $extraPlugins;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'config_name'      => self::CONFIG_NAME,
                'contents_css'     => null,
                'contents_css_dir' => $this->contentsCssDir,
                'enable_widgets'   => false,
            ])
            ->setAllowedTypes('config_name', 'string')
            ->setAllowedTypes('contents_css', ['string', 'string[]', 'null'])
            ->setAllowedTypes('contents_css_dir', 'string')
            ->setAllowedTypes('enable_widgets', 'boolean');
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return \FOS\CKEditorBundle\Form\Type\CKEditorType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_ckeditor';
    }

    /**
     * @param \Symfony\Component\Form\FormView $view    Form view
     * @param array                            $options Options
     */
    private function applyContentsCss(FormView $view, array $options): void
    {
        $paths = $options['contents_css'];

        if (null === $paths) {
            return;
        }
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        if (empty($paths)) {
            return;
        }
        if (!isset($view->vars['config']['contentsCss'])) {
            $view->vars['config']['contentsCss'] = [];
        }
        if (!is_array($view->vars['config']['contentsCss'])) {
            $view->vars['config']['contentsCss'] = [$view->vars['config']['contentsCss']];
        }
        foreach ($paths as $path) {
            if (false === strpos($path, '.')) {
                $path .= '.css';
            }
            if (0 !== strpos($path, '/')) {
                $path = implode(DIRECTORY_SEPARATOR, [$options['contents_css_dir'], $path]);
            }

            $view->vars['config']['contentsCss'][] = $this->assetPackages->getUrl($path);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form Form
     *
     * @return string|null
     */
    private function getLocale(FormInterface $form): ?string
    {
        if (null === $form->getParent()) {
            return null;
        }

        $entity = $form->getParent()->getData();

        if (!$entity instanceof TranslationInterface) {
            return null;
        }

        return $this->propertyAccessor->getValue($entity, $this->translatableManager->getTranslationLocaleProperty());
    }

    /**
     * @param \Symfony\Component\Form\FormView $view Form view
     */
    private function removeExtraPluginsFromBlacklist(FormView $view): void
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
