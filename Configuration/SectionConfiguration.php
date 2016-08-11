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
     * @var \Darvin\AdminBundle\Configuration\Section[]
     */
    private $sectionByEntities;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     * @param array[]                                        $configs     Configs
     *
     * @throws \Darvin\AdminBundle\Configuration\ConfigurationException
     */
    public function __construct(ObjectNamerInterface $objectNamer, array $configs)
    {
        $this->sectionByEntities = [];

        foreach ($configs as $config) {
            $alias = !empty($config['alias']) ? $config['alias'] : $objectNamer->name($config['entity']);

            if (isset($this->sectionByEntities[$alias])) {
                throw new ConfigurationException(sprintf('Section with alias "%s" already exists.', $alias));
            }

            $this->sectionByEntities[$config['entity']] = new Section($alias, $config['entity'], $config['config']);
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
