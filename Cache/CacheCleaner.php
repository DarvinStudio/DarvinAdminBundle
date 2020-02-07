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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Cache cleaner
 */
class CacheCleaner implements CacheCleanerInterface
{
    /**
     * @var array
     */
    private $commands;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger Logger
     *
     * CacheManager constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->commands = [];
    }

    /**
     * {@inheritDoc}
     */
    public function addCacheClearCommand(string $type, string $name, Command $cacheClearCommand, array $input): void
    {
        if (!isset($this->commands[$type])) {
            $this->commands[$type] = [];
        }

        $this->commands[$type][$name] = [$cacheClearCommand, $input];
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheClearCommands(string $type): array
    {
        return $this->commands[$type] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $type, array $commandIds = null): int
    {
        try {
            if (empty($this->commands[$type])) {
                return 1;
            }

            if (null === $commandIds) {
                /** @var \Symfony\Component\Console\Command\Command $command */
                foreach ($this->getCacheClearCommand($type) as list($command, $input)) {
                    $result = $command->run(new ArrayInput($input), new NullOutput());
    
                    if ($result > 0) {
                        return $result;
                    }
                }
            } elseif (!empty($commandIds)) {
                /** @var \Symfony\Component\Console\Command\Command $command */
                foreach ($commandIds as $commandId) {
                    list($command, $input) = $this->commands[$type][$commandId];
                    $result = $command->run(new ArrayInput($input), new NullOutput());

                    if ($result > 0) {
                        return $result;
                    }
                }
            }
        } catch(\Exception $ex) {
            $this->logger->error($ex->getMessage());

            return 1;
        }

        return 0;
    }
}
