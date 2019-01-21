<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Ace editor form type
 */
class AceEditorType extends AbstractType
{
    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @var array
     */
    private $defaultStyle;

    /**
     * @param array $defaultConfig Default configuration
     * @param array $defaultStyle  Default style
     */
    public function __construct(array $defaultConfig, array $defaultStyle)
    {
        $this->defaultConfig = $defaultConfig;
        $this->defaultStyle = $defaultStyle;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['config'] = array_merge($this->defaultConfig, $options['config']);
        $view->vars['style']  = array_merge($this->defaultStyle, $options['style']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'config'   => [],
                'style'    => [],
                'required' => false,
            ])
            ->setAllowedTypes('config', 'array')
            ->setAllowedTypes('style', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_ace_editor';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
