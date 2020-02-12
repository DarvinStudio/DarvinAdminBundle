<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Cache;

/**
 * Cache cleaner
 */
interface CacheCleanerInterface
{
    /**
     * @param string $set Commands set
     *
     * @return array
     */
    public function getAliases(string $set): array;

    /**
     * @param string            $set     Commands set
     * @param array|string|null $aliases Command aliases
     *
     * @return bool
     */
    public function hasCommands(string $set, $aliases = null): bool;

    /**
     * @param string            $set     Commands set
     * @param array|string|null $aliases Command aliases
     *
     * @return int
     */
    public function runCommands(string $set, $aliases = null): int;
}
