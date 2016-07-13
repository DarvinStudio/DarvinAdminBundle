<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Compact CKEditor form type
 */
class CKEditorCompactType extends AbstractType
{
    const CONFIG_NAME = 'darvin_admin_compact';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack Request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $config = $view->vars['config'];

        if (isset($config['language']) && !empty($config['language'])) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return;
        }

        $config['language'] = $request->getLocale();

        $view->vars['config'] = $config;
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
    public function getBlockPrefix()
    {
        return 'darvin_admin_ckeditor_compact';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Ivory\CKEditorBundle\Form\Type\CKEditorType';
    }
}
