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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * List view widgets command
 */
class ListViewWidgetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('darvin:admin:widget:list')
            ->setDescription('Displays list of existing view widgets.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $aliases = $this->getViewWidgetPool()->getWidgetAliases();
        sort($aliases);

        $io->listing($aliases);
    }

    /**
     * @return \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    private function getViewWidgetPool()
    {
        return $this->getContainer()->get('darvin_admin.view.widget.pool');
    }
}
