<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

/**
 * Security configuration pool
 */
class SecurityConfigurationPool
{
    /**
     * @var \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface[]
     */
    private $configurations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configurations = array();
    }

    /**
     * @param \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface $configuration Security configuration
     *
     * @throws \Darvin\AdminBundle\Security\Configuration\ConfigurationException
     */
    public function add(SecurityConfigurationInterface $configuration)
    {
        if (isset($this->configurations[$configuration->getName()])) {
            throw new ConfigurationException(sprintf('Configuration "%s" already added.', $configuration->getName()));
        }

        $this->configurations[$configuration->getName()] = $configuration;
    }

    /**
     * @return \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface[]
     */
    public function getAll()
    {
        return $this->configurations;
    }
}
