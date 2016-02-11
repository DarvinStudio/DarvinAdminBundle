<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Command;

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Assets compile command
 */
class AssetsCompileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('darvin:admin:assets:compile')
            ->setDescription('Compiles admin bundle assets.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $assetCallback = function (AssetInterface $asset) use ($io) {
            $io->comment($asset->getSourceRoot().DIRECTORY_SEPARATOR.$asset->getSourcePath());
        };

        foreach ($this->getAssetCompilerPool()->getCompilers() as $compiler) {
            $compiler->compileAssets($assetCallback);

            $io->note(sprintf('Do not forget to commit the "%s" file.', $compiler->getCompiledAssetPathname()));
        }
    }

    /**
     * @return \Darvin\AdminBundle\Asset\Compiler\AssetCompilerPool
     */
    private function getAssetCompilerPool()
    {
        return $this->getContainer()->get('darvin_admin.asset.compiler.pool');
    }
}
