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
use Darvin\AdminBundle\Asset\Compiler\AssetCompilerPool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Compile assets command
 */
class CompileAssetsCommand extends Command
{
    /**
     * @var \Darvin\AdminBundle\Asset\Compiler\AssetCompilerPool
     */
    private $assetCompilerPool;

    /**
     * @param string                                               $name              Command name
     * @param \Darvin\AdminBundle\Asset\Compiler\AssetCompilerPool $assetCompilerPool Asset compiler pool
     */
    public function __construct($name, AssetCompilerPool $assetCompilerPool)
    {
        parent::__construct($name);

        $this->assetCompilerPool = $assetCompilerPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Compiles admin bundle assets.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('');

        $assetCallback = function (AssetInterface $asset) use ($io) {
            $io->progressAdvance();
            $io->comment($asset->getSourceRoot().DIRECTORY_SEPARATOR.$asset->getSourcePath());
        };

        foreach ($this->assetCompilerPool->getCompilers() as $compiler) {
            $io->section('Compiling '.$compiler->getCompiledAssetPathname());
            $io->progressStart($compiler->getDevAssetsCount());

            $compiler->compileAssets($assetCallback);

            $io->note(sprintf('Do not forget to commit the "%s" file :)', $compiler->getCompiledAssetPathname()));
        }
    }
}
