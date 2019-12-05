<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\Widget\Widget;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Doctrine\Common\Util\ClassUtils;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

/**
 * Image link view widget
 */
class ImageLinkWidget extends AbstractWidget
{
    public const ALIAS = 'image_link';

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContent($entity, array $options): ?string
    {
        $image = null !== $options['property'] ? $this->getPropertyValue($entity, $options['property']) : $entity;

        if (null === $image) {
            return null;
        }
        if (!is_object($image)) {
            throw new \InvalidArgumentException(sprintf('Image must be object, "%s" provided.', gettype($image)));
        }
        if (!$image instanceof AbstractImage) {
            throw new \InvalidArgumentException(
                sprintf('Image object "%s" must be instance of "%s".', ClassUtils::getClass($image), AbstractImage::class)
            );
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
