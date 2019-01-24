<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Form Twig extension
 */
class FormExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): iterable
    {
        yield new TwigFunction('darvin_admin_is_security_configuration_form', [$this, 'isSecurityConfigurationForm']);
    }

    /**
     * @param \Symfony\Component\Form\FormView $form Form
     *
     * @return bool
     */
    public function isSecurityConfigurationForm(FormView $form): bool
    {
        return $form->vars['data'] instanceof SecurityConfigurationInterface;
    }
}
