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
    private $sections;

    /**
     * @param \Darvin\Utils\ObjectNamer\ObjectNamerInterface $objectNamer Object namer
     * @param array[]                                        $configs     Configs
     *
     * @throws \Darvin\AdminBundle\Configuration\ConfigurationException
     */
    public function __construct(ObjectNamerInterface $objectNamer, array $configs)
    {
        $this->sections = [];

        foreach ($configs as $config) {
            $alias = !empty($config['alias']) ? $config['alias'] : $objectNamer->name($config['entity']);

            if (isset($this->sections[$alias])) {
                throw new ConfigurationException(sprintf('Section with alias "%s" already exists.', $alias));
            }

            $this->sections[$alias] = new Section($alias, $config['entity'], $config['config']);
        }
    }

    /**
     * @return \Darvin\AdminBundle\Configuration\Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }
}
