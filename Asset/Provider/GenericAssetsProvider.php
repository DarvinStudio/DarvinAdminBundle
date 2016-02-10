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

/**
 * Assets provider generic implementation
 */
class GenericAssetsProvider implements AssetsProviderInterface
{
    /**
     * @var string
     */
    private $webDir;

    /**
     * @var array
     */
    private $webPathnames;

    /**
     * @var array
     */
    private $absolutePathnames;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param string $webDir       Web directory
     * @param array  $webPathnames Asset web pathnames
     */
    public function __construct($webDir, array $webPathnames)
    {
        $this->webDir = $webDir;
        $this->webPathnames = $webPathnames;
        $this->absolutePathnames = array();
        $this->initialized = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetAbsolutePathnames()
    {
        $this->init();

        return $this->absolutePathnames;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetWebPathnames()
    {
        return $this->webPathnames;
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        foreach ($this->webPathnames as $webPathname) {
            $this->absolutePathnames[] = $this->webDir.DIRECTORY_SEPARATOR.$webPathname;
        }
    }
}
