<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Locale form type
 */
class LocaleType extends AbstractType
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * @param string[] $locales Locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->buildChoices(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_locale';
    }

    /**
     * @return array
     */
    private function buildChoices(): array
    {
        $choices = [];

        foreach ($this->locales as $locale) {
            $choices['locale.'.$locale] = $locale;
        }

        return $choices;
    }
}
