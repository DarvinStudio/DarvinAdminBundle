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

use Symfony\Component\Console\Command\Command;

/**
 * Cache cleaner interface
 */
interface CacheCleanerInterface
{
    /**
     * @param string                                     $type              Type of caches list
     * @param string                                     $name              Name cache clear command
     * @param \Symfony\Component\Console\Command\Command $cacheClearCommand Cache clear command
     * @param array                                      $input             Input
     */
    public function addCacheClearCommand(string $type, string $name, Command $cacheClearCommand, array $input): void;

    /**
     * @param string $type
     *
     * @return array
     */
    public function getCacheClearCommands(string $type): array;

    /**
     * @param string     $type       Type of commands
     * @param null|array $commandIds Array of Ids
     *
     * @return int
     */
    public function run(string $type, array $commandIds = null): int;
}
