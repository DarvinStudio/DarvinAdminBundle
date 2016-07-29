<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\AdminBundle\Configuration\Configuration;
use Darvin\AdminBundle\View\Widget\WidgetException;
use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Darvin\ImageBundle\UrlBuilder\Filter\ResizeFilter;
use Darvin\ImageBundle\UrlBuilder\UrlBuilderInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Image link view widget
 */
class ImageLinkWidget extends AbstractWidget
{
    const ALIAS = 'image_link';

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
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options, $property)
    {
        $image = isset($options['property']) ? $this->getPropertyValue($entity, $options['property']) : $entity;

        if (empty($image)) {
            return '';
        }
        if (!is_object($image)) {
            throw new WidgetException(sprintf('Image must be object, "%s" provided.', gettype($image)));
        }
        if (!$image instanceof AbstractImage) {
            $message = sprintf(
                'Image object "%s" must be instance of "%s".',
                ClassUtils::getClass($image),
                AbstractImage::ABSTRACT_IMAGE_CLASS
            );

            throw new WidgetException($message);
        }
        if (!$this->imageUrlBuilder->fileExists($image)) {
            return '';
        }

        return $this->render($options, [
            'filtered_url' => $this->imageUrlBuilder->buildUrlToFilter(
                $image,
                ResizeFilter::NAME,
                $options['filter_params']
            ),
            'name'         => $image->getName(),
            'original_url' => $this->imageUrlBuilder->buildUrlToOriginal($image),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('filter_params', [
                'size_name' => Configuration::IMAGE_SIZE_ADMIN,
                'outbound'  => true,
            ])
            ->setDefined('property')
            ->setAllowedTypes('filter_params', 'array')
            ->setAllowedTypes('property', 'string');
    }
}
