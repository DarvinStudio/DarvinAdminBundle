<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Twig\Extension;

use Darvin\AdminBundle\Configuration\Configuration;
use Darvin\ImageBundle\UrlBuilder\Filter\ResizeFilter;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;

/**
 * Image Twig extension
 */
class ImageExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Configuration\Configuration
     */
    private $configuration;

    /**
     * @var \Darvin\ImageBundle\UrlBuilder\Filter\ResizeFilter
     */
    private $resizeFilter;

    /**
     * @param \Darvin\AdminBundle\Configuration\Configuration    $configuration Admin configuration
     * @param \Darvin\ImageBundle\UrlBuilder\Filter\ResizeFilter $resizeFilter  Image resize filter
     */
    public function __construct(Configuration $configuration, ResizeFilter $resizeFilter)
    {
        $this->configuration = $configuration;
        $this->resizeFilter = $resizeFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('admin_image_crop', [$this, 'cropImage']),
            new \Twig_SimpleFilter('admin_image_resize', [$this, 'resizeImage']),
        ];
    }

    /**
     * @param string $pathname Image pathname
     *
     * @return string
     */
    public function cropImage($pathname)
    {
        $parameters = $this->getResizeFilterParameters();
        $parameters['outbound'] = true;

        try {
            return $this->resizeFilter->buildUrl($pathname, $parameters);
        } catch (NotLoadableException $ex) {
            return null;
        }
    }

    /**
     * @param string $pathname Image pathname
     *
     * @return string
     */
    public function resizeImage($pathname)
    {
        try {
            return $this->resizeFilter->buildUrl($pathname, $this->getResizeFilterParameters());
        } catch (NotLoadableException $ex) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_image_extension';
    }

    /**
     * @return array
     */
    private function getResizeFilterParameters()
    {
        $size = $this->getAdminImageSize();

        return [
            'width'  => $size->getWidth(),
            'height' => $size->getHeight(),
        ];
    }

    /**
     * @return \Darvin\ImageBundle\Size\Size
     */
    private function getAdminImageSize()
    {
        $sizes = $this->configuration->getImageSizes();

        return $sizes[Configuration::IMAGE_SIZE_ADMIN];
    }
}
