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

/**
 * Assets dumper
 */
interface AssetsDumperInterface
{
    /**
     * @param callable $assetPathnameCallback Asset pathname process callback
     */
    public function dumpAssets(callable $assetPathnameCallback = null);
}
