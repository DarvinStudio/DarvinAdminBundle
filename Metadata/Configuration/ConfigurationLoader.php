<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 06.08.15
 * Time: 12:06
 */

namespace Darvin\AdminBundle\Metadata\Configuration;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration loader
 */
class ConfigurationLoader
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Configuration\Configuration
     */
    private $configuration;

    /**
     * @param \Darvin\AdminBundle\Metadata\Configuration\Configuration $configuration Configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return array
     */
    public function load($pathname)
    {
        $config = $this->getConfig($pathname);

        return $this->processConfiguration($config, $pathname);
    }

    /**
     * @param array  $config   Config
     * @param string $pathname Configuration file pathname
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function processConfiguration(array $config, $pathname)
    {
        $processor = new Processor();

        try {
            return $processor->processConfiguration($this->configuration, $config);
        } catch (InvalidConfigurationException $ex) {
            throw new ConfigurationException(
                sprintf('Configuration file "%s" is invalid: "%s".', $pathname, $ex->getMessage())
            );
        }
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function getConfig($pathname)
    {
        $content = @file_get_contents($pathname);

        if (false === $content) {
            throw new ConfigurationException(sprintf('Unable to get content of configuration file "%s".', $pathname));
        }
        try {
            return Yaml::parse($content);
        } catch (ParseException $ex) {
            throw new ConfigurationException(
                sprintf('Unable to parse configuration file "%s": "%s".', $pathname, $ex->getMessage())
            );
        }
    }
}
