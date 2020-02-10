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
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->commands = [];
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface|null $logger Logger
     */
    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string                                     $type              Type of caches list
     * @param string                                     $name              Name cache clear command
     * @param \Symfony\Component\Console\Command\Command $cacheClearCommand Cache clear command
     * @param array                                      $input             Input
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
        if (null !== $commandIds && !is_array($commandIds)) {
            $commandIds = [$commandIds];
        }
        try {
            if (empty($this->commands[$type])) {
                return 1;
            }

            if (null === $commandIds) {
                /** @var \Symfony\Component\Console\Command\Command $command */
                foreach ($this->getCacheClearCommands($type) as list($command, $input)) {
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
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());

            return 1;
        }

        return 0;
    }
}
