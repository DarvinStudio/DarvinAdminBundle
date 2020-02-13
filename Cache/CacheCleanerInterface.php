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
     * @param string $set Command set
     *
     * @return string[]
     * @throws \InvalidArgumentException
     */
    public function getCommandAliases(string $set): array;

    /**
     * @param string $set Command set
     *
     * @return bool
     */
    public function hasCommands(string $set): bool;

    /**
     * @param string            $set     Command set
     * @param array|string|null $aliases Command aliases
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function runCommands(string $set, $aliases = null): int;
}
