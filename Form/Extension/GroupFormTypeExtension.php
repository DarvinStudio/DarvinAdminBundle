<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Group form type extension
 */
class GroupFormTypeExtension extends AbstractTypeExtension
{
    private const OPTIONS = [
        'admin_group',
        'admin_spoiler',
        'admin_tab',
    ];

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach (self::OPTIONS as $option) {
            $view->vars[$option] = $options[$option];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        foreach (self::OPTIONS as $option) {
            $resolver
                ->setDefault($option, null)
                ->setAllowedTypes($option, ['string', 'null']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield FormType::class;
    }
}
