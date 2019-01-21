<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\Utils\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Compact CKEditor form type
 */
class CKEditorCompactType extends AbstractType
{
    const CONFIG_NAME = 'darvin_admin_compact';

    /**
     * @var \Darvin\Utils\Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @param \Darvin\Utils\Locale\LocaleProviderInterface $localeProvider Locale provider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $config = $view->vars['config'];

        if (!isset($config['language'])) {
            $config['language'] = $this->localeProvider->getCurrentLocale();

            $view->vars['config'] = $config;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('config_name', self::CONFIG_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return CKEditorType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_ck_editor_compact';
    }
}
