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

/**
 * Asset compiler
 */
interface AssetCompilerInterface
{
    /**
     * @param callable $assetCallback Asset process callback
     */
    public function compileAssets(callable $assetCallback = null);

    /**
     * @return string
     */
    public function getCompiledAssetPathname();
}
