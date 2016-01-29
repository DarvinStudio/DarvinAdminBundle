<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Darvin\ImageBundle\UrlBuilder\Filter\ResizeFilter;
use Darvin\ImageBundle\UrlBuilder\UrlBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Image link view widget generator
 */
class ImageLinkGenerator extends AbstractWidgetGenerator
{
    /**
     * @var \Darvin\ImageBundle\UrlBuilder\UrlBuilderInterface
     */
    private $imageUrlBuilder;

    /**
     * @param \Darvin\ImageBundle\UrlBuilder\UrlBuilderInterface $imageUrlBuilder Image URL builder
     */
    public function setImageUrlBuilder(UrlBuilderInterface $imageUrlBuilder)
    {
        $this->imageUrlBuilder = $imageUrlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'image_link';
    }

    /**
     * {@inheritdoc}
     */
    protected function generateWidget($entity, array $options)
    {
        /** @var \Darvin\ImageBundle\Entity\Image\AbstractImage $entity */
        if (!$this->imageUrlBuilder->fileExists($entity)) {
            return '';
        }

        return $this->render($options, array(
            'filtered_url' => $this->imageUrlBuilder->buildUrlToFilter(
                $entity,
                ResizeFilter::NAME,
                $options['filter_parameters']
            ),
            'name'         => $entity->getName(),
            'original_url' => $this->imageUrlBuilder->buildUrlToOriginal($entity),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array(
                'filter_parameters',
            ))
            ->setAllowedTypes('filter_parameters', 'array');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredEntityClass()
    {
        return AbstractImage::ABSTRACT_IMAGE_CLASS;
    }
}
