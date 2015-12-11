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
 * View widget generator aliases list command
 */
class WidgetGeneratorsListCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('darvin:admin:widget:list')
            ->setDescription('Displays list of aliases of existing view widget generators.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->table(array('Alias'), array_map(function ($alias) {
            return array($alias);
        }, $this->getViewWidgetGeneratorPool()->getAllWidgetGeneratorAliases()));
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private function getViewWidgetGeneratorPool()
    {
        return $this->getContainer()->get('darvin_admin.view.widget_generator.pool');
    }
}
