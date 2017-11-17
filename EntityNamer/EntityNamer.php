<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EntityNamer;

use Darvin\AdminBundle\Configuration\SectionConfiguration;
use Darvin\Utils\ObjectNamer\ObjectNamerInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Entity namer
 */
class EntityNamer implements EntityNamerInterface
{
    /**
     * @var \Darvin\Utils\ObjectNamer\ObjectNamerInterface
     */
    private $objectNamer;

    /**
     * @var \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private $sectionConfig;

    /**
     * @var array
     */
    private $entityOverride;

    /**
     * @var string[]
     */
    private $names;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface         $objectNamer    Object namer
     * @param \Darvin\AdminBundle\Configuration\SectionConfiguration $sectionConfig  Section configuration
     * @param array                                                  $entityOverride Entity override configuration
     */
    public function __construct(ObjectNamerInterface $objectNamer, SectionConfiguration $sectionConfig, array $entityOverride)
    {
        $this->objectNamer = $objectNamer;
        $this->sectionConfig = $sectionConfig;
        $this->entityOverride = $entityOverride;

        $this->names = [];
    }

    /**
     * {@inheritdoc}
     */
    public function name($entityOrClass)
    {
        $class = is_object($entityOrClass) ? ClassUtils::getClass($entityOrClass) : $entityOrClass;

        if (!isset($this->names[$class])) {
            $this->names[$class] = $this->getName($class);
        }

        return $this->names[$class];
    }

    /**
     * @param string $class Entity class
     *
     * @return string
     */
    private function getName($class)
    {
        $class = preg_replace('/(.*[^\\\]+)Interface$/', '$1', $class);

        if (isset($this->entityOverride[$class])) {
            $class = $this->entityOverride[$class];
        }
        if ($this->sectionConfig->hasSection($class)) {
            return $this->sectionConfig->getSection($class)->getAlias();
        }

        return $this->objectNamer->name($class);
    }
}
