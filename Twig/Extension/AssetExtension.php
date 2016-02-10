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

use Darvin\AdminBundle\Asset\Provider\AssetsProviderPool;

/**
 * Asset Twig extension
 */
class AssetExtension extends \Twig_Extension
{
    /**
     * @var \Darvin\AdminBundle\Asset\Provider\AssetsProviderPool
     */
    private $assetProviderPool;

    /**
     * @param \Darvin\AdminBundle\Asset\Provider\AssetsProviderPool $assetsProviderPool Asset provider pool
     */
    public function __construct(AssetsProviderPool $assetsProviderPool)
    {
        $this->assetProviderPool = $assetsProviderPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('admin_assets', array($this->assetProviderPool, 'getAssetWebPathnames')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_admin_asset_extension';
    }
}
