<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Asset\Dumper;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterInterface;
use Darvin\AdminBundle\Asset\AssetException;
use Darvin\AdminBundle\Asset\Provider\AssetsProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Assets dumper generic implementation
 */
class GenericAssetsDumper implements AssetsDumperInterface
{
    /**
     * @var \Darvin\AdminBundle\Asset\Provider\AssetsProviderInterface
     */
    private $assetsProvider;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var \Assetic\Filter\FilterInterface[]
     */
    private $filters;

    /**
     * @param \Darvin\AdminBundle\Asset\Provider\AssetsProviderInterface $assetsProvider Assets provider
     * @param \Symfony\Component\HttpKernel\KernelInterface              $kernel         App kernel
     * @param string                                                     $destination    Destination relative to bundle
     */
    public function __construct(AssetsProviderInterface $assetsProvider, KernelInterface $kernel, $destination)
    {
        $this->assetsProvider = $assetsProvider;
        $this->kernel = $kernel;
        $this->destination = $destination;
        $this->filters = array();
    }

    /**
     * @param \Assetic\Filter\FilterInterface $filter Assetic filter
     *
     * @throws \Darvin\AdminBundle\Asset\AssetException
     */
    public function addFilter(FilterInterface $filter)
    {
        $class = get_class($filter);

        if (isset($this->filters[$class])) {
            throw new AssetException(sprintf('Assetic filter "%s" already added to assets dumper "%s".', $class, get_class($this)));
        }

        $this->filters[$class] = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpAssets(callable $assetPathnameCallback = null)
    {
        $basePath = $this->kernel->getBundle('DarvinAdminBundle')->getPath();

        $collection = new AssetCollection(array(), $this->filters);

        foreach ($this->assetsProvider->getAssetAbsolutePathnames() as $pathname) {
            $collection->add(new FileAsset($pathname));

            if (!empty($assetPathnameCallback)) {
                $assetPathnameCallback($pathname);
            }
        }

        $fs = new Filesystem();
        $fs->dumpFile($basePath.DIRECTORY_SEPARATOR.$this->destination, $collection->dump());
    }
}
