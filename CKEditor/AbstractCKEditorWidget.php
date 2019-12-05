<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\CKEditor;

use Darvin\ContentBundle\Widget\AbstractWidget;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKEditor widget abstract implementation
 */
abstract class AbstractCKEditorWidget extends AbstractWidget implements CKEditorWidgetInterface
{
    private const ICON = __DIR__.'/../Resources/images/ckeditor_stub.png';

    /**
     * @var array|null
     */
    private $resolvedOptions = null;

    /**
     * {@inheritDoc}
     */
    public function getResolvedOptions(): array
    {
        if (null === $this->resolvedOptions) {
            $resolver = new OptionsResolver();

            $this->configureOptions($resolver);

            $this->resolvedOptions = $resolver->resolve($this->getOptions());
        }

        return $this->resolvedOptions;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'icon'          => self::ICON,
                'letter_source' => null,
                'title'         => sprintf('ckeditor_widget.%s', $this->getName()),
                'show_letter'   => function (Options $options) {
                    return $options['icon'] === self::ICON;
                },
            ])
            ->setAllowedTypes('icon', 'string')
            ->setAllowedTypes('letter_source', ['string', 'null'])
            ->setAllowedTypes('show_letter', 'boolean')
            ->setAllowedTypes('title', 'string');
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }
}
