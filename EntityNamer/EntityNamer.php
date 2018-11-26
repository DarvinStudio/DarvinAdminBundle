<?php declare(strict_types=1);
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
use Darvin\Utils\ORM\EntityResolverInterface;

/**
 * Entity namer
 */
class EntityNamer implements EntityNamerInterface
{
    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

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
    private $names;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface              $entityResolver Entity resolver
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface         $objectNamer    Object namer
     * @param \Darvin\AdminBundle\Configuration\SectionConfiguration $sectionConfig  Section configuration
     */
    public function __construct(EntityResolverInterface $entityResolver, ObjectNamerInterface $objectNamer, SectionConfiguration $sectionConfig)
    {
        $this->entityResolver = $entityResolver;
        $this->objectNamer = $objectNamer;
        $this->sectionConfig = $sectionConfig;

        $this->names = [];
    }

    /**
     * {@inheritdoc}
     */
    public function name($entity): string
    {
        $class = is_object($entity) ? get_class($entity) : $entity;

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
    private function getName(string $class): string
    {
        $class = $this->entityResolver->resolve($class);

        if ($this->sectionConfig->hasSection($class)) {
            return $this->sectionConfig->getSection($class)->getAlias();
        }

        return $this->objectNamer->name($class);
    }
}
