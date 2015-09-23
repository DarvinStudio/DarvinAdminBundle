<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\View\WidgetGenerator;

/**
 * View widget generator pool
 */
class WidgetGeneratorPool
{
    /**
     * @var \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface[]
     */
    private $generators;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->generators = array();
    }

    /**
     * @param \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface $generator View widget generator
     *
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    public function addWidgetGenerator(WidgetGeneratorInterface $generator)
    {
        $alias = $generator->getAlias();

        if (isset($this->generators[$alias])) {
            throw new WidgetGeneratorException(sprintf('View widget generator alias "%s" already used.', $alias));
        }

        $this->generators[$alias] = $generator;
    }

    /**
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface[]
     */
    public function getAllWidgetGenerators()
    {
        return $this->generators;
    }

    /**
     * @param string $alias View widget generator alias
     *
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    public function getWidgetGenerator($alias)
    {
        if (!isset($this->generators[$alias])) {
            throw new WidgetGeneratorException(sprintf('Unable to find view widget generator by alias "%s".', $alias));
        }

        return $this->generators[$alias];
    }
}
