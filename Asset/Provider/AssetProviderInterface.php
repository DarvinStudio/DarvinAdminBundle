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
 * Asset provider
 */
interface AssetProviderInterface
{
    /**
     * @return array
     */
    public function getDevAssetAbsolutePathnames();

    /**
     * @return string
     */
    public function getCompiledAssetAbsolutePathname();

    /**
     * @return array
     */
    public function getAssetWebPathnames();
}
