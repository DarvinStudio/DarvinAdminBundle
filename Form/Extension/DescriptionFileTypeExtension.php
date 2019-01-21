<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description file form type extension
 */
class DescriptionFileTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var int
     */
    private $maxUploadSize;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator    Translator
     * @param mixed                                              $maxUploadSize Max upload size in MB
     */
    public function __construct(TranslatorInterface $translator, $maxUploadSize)
    {
        $this->translator = $translator;
        $this->maxUploadSize = (int)$maxUploadSize;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $view->vars['description'] || null === $form->getParent()) {
            return;
        }

        $f = $form;

        while ($parent = $f->getParent()) {
            if ($parent->getConfig()->getType()->getInnerType() instanceof EntityType) {
                $view->vars['description'] = $this->translator->trans('form.file.description', [
                    '%size%' => $this->maxUploadSize,
                ], 'admin');

                return;
            }

            $f = $parent;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield FileType::class;
    }
}
