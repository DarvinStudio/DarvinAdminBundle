<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Photo gallery view widget
 */
class PhotoGalleryWidget extends EntityListWidget
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'item_widget_alias'   => ImageLinkWidget::ALIAS,
                'item_widget_options' => [],
                'line_size'           => 5,
            ])
            ->setAllowedTypes('line_size', 'integer');
    }
}
