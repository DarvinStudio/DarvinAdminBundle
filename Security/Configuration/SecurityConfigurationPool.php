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

use Darvin\ConfigBundle\Security\Authorization\ConfigurationAuthorizationChecker;

/**
 * Security configuration pool
 */
class SecurityConfigurationPool
{
    /**
     * @var \Darvin\ConfigBundle\Security\Authorization\ConfigurationAuthorizationChecker
     */
    private $configurationAuthorizationChecker;

    /**
     * @var \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface[]
     */
    private $configurations;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param \Darvin\ConfigBundle\Security\Authorization\ConfigurationAuthorizationChecker $configurationAuthorizationChecker Configuration authorization checker
     */
    public function __construct(ConfigurationAuthorizationChecker $configurationAuthorizationChecker)
    {
        $this->configurationAuthorizationChecker = $configurationAuthorizationChecker;
        $this->configurations = array();
        $this->initialized = false;
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
        $this->init();

        return $this->configurations;
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }
        foreach ($this->configurations as $name => $configuration) {
            if (!$this->configurationAuthorizationChecker->isAccessible($configuration)) {
                unset($this->configurations[$name]);
            }
        }

        $this->initialized = true;
    }
}
