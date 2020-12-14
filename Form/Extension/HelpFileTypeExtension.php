<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Extension;

use Darvin\AdminBundle\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Help file form type extension
 */
class HelpFileTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var int
     */
    private $uploadMaxSizeMb;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator      Translator
     * @param int                                                $uploadMaxSizeMb Max upload size in MB
     */
    public function __construct(TranslatorInterface $translator, int $uploadMaxSizeMb)
    {
        $this->translator = $translator;
        $this->uploadMaxSizeMb = $uploadMaxSizeMb;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $view->vars['help'] || null === $form->getParent()) {
            return;
        }

        $f = $form;

        while ($parent = $f->getParent()) {
            if ($parent->getConfig()->getType()->getInnerType() instanceof EntityType) {
                $view->vars['help'] = $this->translator->trans('form.generic_file.help', [
                    '%size%' => $options['upload_max_size_mb'],
                ], 'admin');

                return;
            }

            $f = $parent;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('upload_max_size_mb', $this->uploadMaxSizeMb)
            ->setAllowedTypes('upload_max_size_mb', 'integer');
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield FileType::class;
    }
}
