<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Photo gallery view widget generator
 */
class PhotoGalleryGenerator extends EntitiesListGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'item_widget_alias'   => ImageLinkGenerator::ALIAS,
            'item_widget_options' => array(),
            'line_size'           => 5,
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTemplate()
    {
        return parent::getDefaultTemplate();
    }
}
