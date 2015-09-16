<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Caches clear command
 */
class CachesClearCommand extends Command
{
    /**
     * @var \Symfony\Component\Console\Command\Command[]
     */
    private $cacheClearCommands;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->cacheClearCommands = array();
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $cacheClearCommand Cache clear command
     */
    public function addCacheClearCommand(Command $cacheClearCommand)
    {
        $this->cacheClearCommands[] = $cacheClearCommand;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandInput = new ArrayInput(array());

        foreach ($this->cacheClearCommands as $command) {
            $output->writeln($command->getName());

            $result = $command->run($commandInput, $output);

            if ($result > 0) {
                return $result;
            }
        }

        return 0;
    }
}
