<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 13.08.15
 * Time: 11:53
 */

namespace Darvin\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List view widget generator aliases command
 */
class ListWidgetGeneratorsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Displays list of aliases of existing view widget generators.')
            ->setName('darvin:admin:list-widgets');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array_keys($this->getViewWidgetGeneratorPool()->getAll()));
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private function getViewWidgetGeneratorPool()
    {
        return $this->getContainer()->get('darvin_admin.view.widget_generator.pool');
    }
}
