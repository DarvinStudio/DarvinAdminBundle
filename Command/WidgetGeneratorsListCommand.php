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

use Darvin\Utils\Command\AbstractContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * View widget generator aliases list command
 */
class WidgetGeneratorsListCommand extends AbstractContainerAwareCommand
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
        parent::execute($input, $output);

        $this->writeln($this->getViewWidgetGeneratorPool()->getAllWidgetGeneratorAliases());
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorPool
     */
    private function getViewWidgetGeneratorPool()
    {
        return $this->getContainer()->get('darvin_admin.view.widget_generator.pool');
    }
}
