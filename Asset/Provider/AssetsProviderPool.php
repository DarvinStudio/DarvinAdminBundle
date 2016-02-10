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
 * Assets provider pool
 */
class AssetsProviderPool
{
    /**
     * @var \Darvin\AdminBundle\Asset\Provider\AssetsProviderInterface[]
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
     * @param string                                                     $alias    Assets provider alias
     * @param \Darvin\AdminBundle\Asset\Provider\AssetsProviderInterface $provider Assets provider
     *
     * @throws \Darvin\AdminBundle\Asset\AssetException
     */
    public function addProvider($alias, AssetsProviderInterface $provider)
    {
        if (isset($this->providers[$alias])) {
            throw new AssetException(sprintf('Assets provider with alias "%s" already added to pool.', $alias));
        }

        $this->providers[$alias] = $provider;
    }

    /**
     * @param string $alias Assets provider alias
     *
     * @return array
     * @throws \Darvin\AdminBundle\Asset\AssetException
     */
    public function getAssetWebPathnames($alias)
    {
        if (!isset($this->providers[$alias])) {
            throw new AssetException(sprintf('There is no assets provider with alias "%s" in pool.', $alias));
        }

        $provider = $this->providers[$alias];

        return $provider->getAssetWebPathnames();
    }
}
