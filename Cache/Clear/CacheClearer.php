<?php declare(strict_types=1);
/**
 * @author    Alexander Volodin <mr-stanlik@yandex.ru>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Cache\Clear;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Cache clearer
 */
class CacheClearer implements CacheClearerInterface
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
     * @var array
     */
    private $clearOnCrudSets;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel Kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        $this->commands = [];
        $this->clearOnCrudSets = [];
    }

    /**
     * @param \Psr\Log\LoggerInterface|null $logger Logger
     */
    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string                                     $set         Command set
     * @param string                                     $alias       Command alias
     * @param \Symfony\Component\Console\Command\Command $command     Command
     * @param array                                      $input       Input
     * @param bool                                       $clearOnCrud Whether to clear cache on CRUD
     */
    public function addCommand(string $set, string $alias, Command $command, array $input, bool $clearOnCrud): void
    {
        if (!isset($this->commands[$set])) {
            $this->commands[$set] = [];
        }

        $this->commands[$set][$alias] = [$command, $input];

        if ($clearOnCrud) {
            $this->clearOnCrudSets[$set] = $set;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCommandAliases(string $set): array
    {
        return array_keys($this->getCommands($set));
    }

    /**
     * {@inheritDoc}
     */
    public function runCommands(string $set, $aliases = null): int
    {
        $application = $this->createApplication();

        /** @var \Symfony\Component\Console\Command\Command $command */
        foreach ($this->getCommands($set, $aliases) as list($command, $input)) {
            $command->setApplication($application);

            try {
                $result = $command->run(new ArrayInput($input), new NullOutput());
            } catch (\Exception $ex) {
                if (null !== $this->logger) {
                    $this->logger->error(implode(' ', [__METHOD__, $ex->getMessage()]));
                }

                return 1;
            }
            if ($result > 0) {
                return $result;
            }
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function clearOnCrud(): int
    {
        foreach ($this->clearOnCrudSets as $set) {
            $result = $this->runCommands($set);

            if ($result > 0) {
                return $result;
            }
        }

        return 0;
    }

    /**
     * @param string            $set     Command set
     * @param array|string|null $aliases Command aliases
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getCommands(string $set, $aliases = null): array
    {
        if (!isset($this->commands[$set])) {
            throw new \InvalidArgumentException(sprintf('Cache clear command set "%s" does not exist.', $set));
        }
        if (null === $aliases) {
            return $this->commands[$set];
        }
        if (!is_array($aliases)) {
            $aliases = [$aliases];
        }

        $commands = [];

        foreach ($aliases as $alias) {
            if (!isset($this->commands[$set][$alias])) {
                throw new \InvalidArgumentException(sprintf('Cache clear command "%s" does not exist in set "%s".', $alias, $set));
            }

            $commands[$alias] = $this->commands[$set][$alias];
        }

        return $commands;
    }

    /**
     * @return \Symfony\Component\Console\Application
     */
    private function createApplication(): Application
    {
        return new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
    }
}
