<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 10:03
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
    public function add(WidgetGeneratorInterface $generator)
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
    public function getAll()
    {
        return $this->generators;
    }

    /**
     * @param string $alias View widget generator alias
     *
     * @return \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface
     * @throws \Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorException
     */
    public function get($alias)
    {
        if (!isset($this->generators[$alias])) {
            throw new WidgetGeneratorException(sprintf('Unable to find view widget generator by alias "%s".', $alias));
        }

        return $this->generators[$alias];
    }
}
