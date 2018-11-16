<?php declare(strict_types=1);
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
        $this->configurations = [];
    }

    /**
     * @param \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface $configuration Security configuration
     */
    public function addConfiguration(SecurityConfigurationInterface $configuration): void
    {
        $this->configurations[$configuration->getName()] = $configuration;
    }

    /**
     * @return \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface[]
     */
    public function getAllConfigurations(): array
    {
        return $this->configurations;
    }
}
