<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Asset\Compiler;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Assetic\Filter\FilterInterface;
use Darvin\AdminBundle\Asset\AssetException;
use Darvin\AdminBundle\Asset\Provider\AssetProviderInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Asset compiler generic implementation
 */
class GenericAssetCompiler implements AssetCompilerInterface
{
    /**
     * @var \Darvin\AdminBundle\Asset\Provider\AssetProviderInterface
     */
    private $assetProvider;

    /**
     * @var \Assetic\Filter\FilterInterface[]
     */
    private $filters;

    /**
     * @param \Darvin\AdminBundle\Asset\Provider\AssetProviderInterface $assetProvider Asset provider
     */
    public function __construct(AssetProviderInterface $assetProvider)
    {
        $this->assetProvider = $assetProvider;

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
            throw new AssetException(sprintf('Assetic filter "%s" already added to asset compiler "%s".', $class, get_class($this)));
        }

        $this->filters[$class] = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function compileAssets(callable $assetCallback = null)
    {
        $collection = new AssetCollection();

        foreach ($this->assetProvider->getDevAssetAbsolutePathnames() as $pathname) {
            $asset = new FileAsset($pathname, $this->filters);

            if (!empty($assetCallback)) {
                $assetCallback($asset);
            }

            $collection->add(new StringAsset($asset->dump()));
        }

        $fs = new Filesystem();

        try {
            $fs->dumpFile($this->assetProvider->getCompiledAssetAbsolutePathname(), $collection->dump());
        } catch (\Exception $ex) {
            throw new AssetException($ex->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDevAssetsCount()
    {
        return count($this->assetProvider->getDevAssetAbsolutePathnames());
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledAssetPathname()
    {
        return $this->assetProvider->getCompiledAssetAbsolutePathname();
    }
}
