<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Translatable form type
 */
class TranslatableType extends AbstractType
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @param string   $defaultLocale Default locale
     * @param string[] $locales       Locales
     */
    public function __construct(string $defaultLocale, array $locales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options['options']['label'] = false;

        foreach ($this->locales as $locale) {
            $builder->add($locale, $options['type'], $options['options']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['default_locale'] = $this->defaultLocale;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'type'    => TextType::class,
                'options' => [],
            ])
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('options', 'array');
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_translatable';
    }
}
