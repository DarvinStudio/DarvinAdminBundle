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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Cache cleaner
 */
class CacheCleaner implements CacheCleanerInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @var array
     */
    private $commands;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel Kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->commands = [];
    }

    /**
     * @param \Psr\Log\LoggerInterface|null $logger Logger
     */
    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string                                     $set     Caches set
     * @param string                                     $alias   Alias cache clear command
     * @param \Symfony\Component\Console\Command\Command $command Cache clear command
     * @param array                                      $input   Input
     */
    public function addCommand(string $set, string $alias, Command $command, array $input): void
    {
        if (!isset($this->commands[$set])) {
            $this->commands[$set] = [];
        }

        $this->commands[$set][$alias] = [$command, $input];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(string $set): array
    {
        return array_keys($this->commands[$set]) ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function hasCommands(string $set, $aliases = null): bool
    {
        if (null !== $aliases && !is_array($aliases)) {
            $aliases = [$aliases];
        }

        return !empty($this->getCommands($set, $aliases));
    }

    /**
     * {@inheritDoc}
     */
    public function runCommands(string $set, $aliases = null): int
    {
        if (null !== $aliases && !is_array($aliases)) {
            $aliases = [$aliases];
        }

        try {
            $application = $this->getApplication();

            /** @var \Symfony\Component\Console\Command\Command $command */
            foreach ($this->getCommands($set, $aliases) as [$command, $input]) {
                $command->setApplication($application);
                $result = $command->run(new ArrayInput($input), new NullOutput());

                if ($result > 0) {
                    return $result;
                }
            }
        } catch (\Exception $ex) {
            if (null !== $this->logger) {
                $this->logger->error($ex->getMessage());
            }

            return 1;
        }

        return 0;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    private function getApplication(): Application
    {
        return new Application($this->kernel);
    }

    /**
     * @param string     $set     Caches set
     * @param array|null $aliases Aliases
     *
     * @return array
     */
    private function getCommands(string $set, ?array $aliases = null): array
    {
        if (empty($this->commands[$set])) {
            return [];
        }

        if (null !== $aliases) {
            return array_intersect_key($this->commands[$set], array_flip($aliases));
        }

        return $this->commands[$set];
    }
}
