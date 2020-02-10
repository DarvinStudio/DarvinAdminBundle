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
     * @param string $type Commands type
     *
     * @return array
     */
    public function getCommands(string $type): array;

    /**
     * @param string            $type    Commands type
     * @param array|string|null $aliases Command aliases
     *
     * @return int
     */
    public function run(string $type, $aliases = null): int;
}
