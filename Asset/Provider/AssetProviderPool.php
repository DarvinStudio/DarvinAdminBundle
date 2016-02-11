<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Asset\Provider;

use Darvin\AdminBundle\Asset\AssetException;

/**
 * Asset provider pool
 */
class AssetProviderPool
{
    /**
     * @var \Darvin\AdminBundle\Asset\Provider\AssetProviderInterface[]
     */
    private $providers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->providers = array();
    }

    /**
     * @param string                                                    $alias    Asset provider alias
     * @param \Darvin\AdminBundle\Asset\Provider\AssetProviderInterface $provider Asset provider
     *
     * @throws \Darvin\AdminBundle\Asset\AssetException
     */
    public function addProvider($alias, AssetProviderInterface $provider)
    {
        if (isset($this->providers[$alias])) {
            throw new AssetException(sprintf('Asset provider with alias "%s" already added to pool.', $alias));
        }

        $this->providers[$alias] = $provider;
    }

    /**
     * @param string $alias Asset provider alias
     *
     * @return array
     * @throws \Darvin\AdminBundle\Asset\AssetException
     */
    public function getAssetWebPathnames($alias)
    {
        if (!isset($this->providers[$alias])) {
            throw new AssetException(sprintf('There is no asset provider with alias "%s" in pool.', $alias));
        }

        $provider = $this->providers[$alias];

        return $provider->getAssetWebPathnames();
    }
}
