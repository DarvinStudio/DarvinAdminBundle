<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * List view widgets command
 */
class ListViewWidgetsCommand extends Command
{
    /**
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private $widgetPool;

    /**
     * @param string                                                  $name       Command name
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface $widgetPool View widget pool
     */
    public function __construct(string $name, ViewWidgetPoolInterface $widgetPool)
    {
        parent::__construct($name);

        $this->widgetPool = $widgetPool;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Displays list of existing view widgets.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $aliases = $this->widgetPool->getWidgetAliases();
        sort($aliases);

        $io->listing($aliases);

        return 0;
    }
}
