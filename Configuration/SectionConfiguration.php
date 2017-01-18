<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Configuration;

use Darvin\Utils\ObjectNamer\ObjectNamerInterface;

/**
 * Section configuration
 */
class SectionConfiguration
{
    /**
     * @var array
     */
    private $sectionByAliases;

    /**
     * @var array
     */
    private $sectionByEntities;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer    Object namer
     * @param array[]                                        $configs        Configs
     * @param array                                          $entityOverride Entity override configuration
     *
     * @throws \Darvin\AdminBundle\Configuration\ConfigurationException
     */
    public function __construct(ObjectNamerInterface $objectNamer, array $configs, array $entityOverride)
    {
        $this->sectionByAliases = $this->sectionByEntities = [];

        foreach ($configs as $config) {
            if (!$config['enabled']) {
                continue;
            }

            $entity = $config['entity'];
            $alias = !empty($config['alias']) ? $config['alias'] : $objectNamer->name($entity);

            if (isset($this->sectionByEntities[$entity])) {
                throw new ConfigurationException(sprintf('Section for entity "%s" already exists.', $entity));
            }
            if (isset($this->sectionByAliases[$alias])) {
                throw new ConfigurationException(sprintf('Section with alias "%s" already exists.', $alias));
            }
            if (isset($entityOverride[$entity])) {
                $entity = $entityOverride[$entity];
            }

            $this->sectionByEntities[$entity] = new Section($alias, $entity, $config['config']);
        }
    }

    /**
     * @param string $entity Entity class
     *
     * @return \Darvin\AdminBundle\Configuration\Section
     * @throws \Darvin\AdminBundle\Configuration\ConfigurationException
     */
    public function getSection($entity)
    {
        if (!$this->hasSection($entity)) {
            throw new ConfigurationException(sprintf('Section for entity "%s" does not exist.', $entity));
        }

        return $this->sectionByEntities[$entity];
    }

    /**
     * @param string $entity Entity class
     *
     * @return bool
     */
    public function hasSection($entity)
    {
        return isset($this->sectionByEntities[$entity]);
    }

    /**
     * @return \Darvin\AdminBundle\Configuration\Section[]
     */
    public function getSections()
    {
        return $this->sectionByEntities;
    }
}
