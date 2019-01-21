<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Darvin\ImageBundle\UrlBuilder\UrlBuilderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

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
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $image = !empty($options['property']) ? $this->getPropertyValue($entity, $options['property']) : $entity;

        if (empty($image)) {
            return null;
        }
        if (!is_object($image)) {
            throw new \InvalidArgumentException(sprintf('Image must be object, "%s" provided.', gettype($image)));
        }
        if (!$image instanceof AbstractImage) {
            throw new \InvalidArgumentException(
                sprintf('Image object "%s" must be instance of "%s".', get_class($image), AbstractImage::class)
            );
        }
        if (!$this->imageUrlBuilder->fileExists($image)) {
            return null;
        }

        try {
            return $this->render([
                'image' => $image,
            ]);
        } catch (NotLoadableException $ex) {
            return null;
        }
    }
}
