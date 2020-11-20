<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Text view widget
 */
class TextWidget extends AbstractWidget
{
    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator Translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $text = (string)$this->getPropertyValue($entity, $options['property']);

        if ($options['trans']) {
            $text = $this->translator->trans($text, [], 'admin');
        }

        $text = trim(preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r", "\n", "\t"], ' ', strip_tags($text))));

        if ('' === $text) {
            return null;
        }
        if (mb_strlen($text) > $options['length']) {
            $text = sprintf('%s...', mb_substr($text, 0, $options['length'] - 3));
        }

        return $this->render([
            'text' => $text,
            'rows' => $options['rows'],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'length' => 80,
                'rows'   => 1,
                'trans'  => false,
            ])
            ->setAllowedTypes('length', 'integer')
            ->setAllowedTypes('rows', 'integer')
            ->setAllowedTypes('trans', 'boolean');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredPermissions(): iterable
    {
        yield Permission::VIEW;
    }
}
