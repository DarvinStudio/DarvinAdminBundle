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

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Asset provider generic implementation
 */
class GenericAssetProvider implements AssetProviderInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var array
     */
    private $devAssetWebPathnames;

    /**
     * @var string
     */
    private $compiledAssetPathname;

    /**
     * @var array
     */
    private $devAssetAbsolutePathnames;

    /**
     * @var string
     */
    private $compiledAssetAbsolutePathname;

    /**
     * @var array
     */
    private $assetWebPathnames;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel                Application kernel
     * @param string                                        $webDir                Web directory
     * @param array                                         $devAssetWebPathnames  Development asset pathnames relative to web root
     * @param string                                        $compiledAssetPathname Compiled asset pathname relative to bundle assets directory
     */
    public function __construct(KernelInterface $kernel, $webDir, array $devAssetWebPathnames, $compiledAssetPathname)
    {
        $this->kernel = $kernel;
        $this->webDir = $webDir;
        $this->devAssetWebPathnames = $devAssetWebPathnames;
        $this->compiledAssetPathname = $compiledAssetPathname;

        $this->devAssetAbsolutePathnames = $this->assetWebPathnames = [];
        $this->compiledAssetAbsolutePathname = null;
        $this->initialized = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDevAssetAbsolutePathnames()
    {
        $this->init();

        return $this->devAssetAbsolutePathnames;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledAssetAbsolutePathname()
    {
        $this->init();

        return $this->compiledAssetAbsolutePathname;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetWebPathnames()
    {
        $this->init();

        return $this->assetWebPathnames;
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        foreach ($this->devAssetWebPathnames as $pathname) {
            $this->devAssetAbsolutePathnames[] = $this->webDir.DIRECTORY_SEPARATOR.$pathname;
        }

        $bundle = $this->kernel->getBundle('DarvinAdminBundle');

        $this->compiledAssetAbsolutePathname = implode(DIRECTORY_SEPARATOR, [
            $bundle->getPath(),
            'Resources/public',
            $this->compiledAssetPathname,
        ]);

        $this->assetWebPathnames = $this->kernel->isDebug()
            ? $this->devAssetWebPathnames
            : [
                implode(DIRECTORY_SEPARATOR, [
                    'bundles',
                    preg_replace('/bundle$/', '', strtolower($bundle->getName())),
                    $this->compiledAssetPathname,
                ]),
            ];
    }
}
