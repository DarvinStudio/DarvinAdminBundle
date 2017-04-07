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
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clear caches command
 */
class ClearCachesCommand extends Command
{
    /**
     * @var array
     */
    private $cacheClearCommands;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, $description = null)
    {
        parent::__construct($name);

        $this->setDescription($description);
        $this->cacheClearCommands = [];
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $cacheClearCommand Cache clear command
     * @param array                                      $input             Input
     */
    public function addCacheClearCommand(Command $cacheClearCommand, array $input)
    {
        $this->cacheClearCommands[] = [$cacheClearCommand, $input];
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var \Symfony\Component\Console\Command\Command $command */
        foreach ($this->cacheClearCommands as list($command, $input)) {
            $io->comment(sprintf('Running "%s" command...', $command->getName()));

            $result = $command->run(new ArrayInput($input), $output);

            if ($result > 0) {
                return $result;
            }
        }

        return 0;
    }
}
