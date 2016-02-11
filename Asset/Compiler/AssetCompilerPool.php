<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Asset\Compiler;

/**
 * Asset compiler pool
 */
class AssetCompilerPool
{
    /**
     * @var \Darvin\AdminBundle\Asset\Compiler\AssetCompilerInterface[]
     */
    private $compilers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->compilers = array();
    }

    /**
     * @param \Darvin\AdminBundle\Asset\Compiler\AssetCompilerInterface $compiler Asset compiler
     */
    public function addCompiler(AssetCompilerInterface $compiler)
    {
        $this->compilers[] = $compiler;
    }

    /**
     * @return \Darvin\AdminBundle\Asset\Compiler\AssetCompilerInterface[]
     */
    public function getCompilers()
    {
        return $this->compilers;
    }
}
