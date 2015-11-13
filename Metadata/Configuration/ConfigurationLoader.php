<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @var array
     */
    private $bundles;

    /**
     * @param \Darvin\AdminBundle\Metadata\Configuration\Configuration $configuration Configuration
     * @param array                                                    $bundles       List of bundles
     */
    public function __construct(Configuration $configuration, array $bundles)
    {
        $this->configuration = $configuration;
        $this->bundles = $bundles;
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    public function load($pathname)
    {
        if (empty($pathname)) {
            throw new ConfigurationException('Configuration file pathname cannot be empty.');
        }

        $config = $this->getMergedConfig($pathname);

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
     */
    private function getMergedConfig($pathname)
    {
        $config = $this->getConfig($pathname);

        if (!isset($config['extends'])) {
            return array($config);
        }

        $child = $config;
        unset($config['extends']);
        $hierarchy = array($config);

        while ($parent = $this->getParentConfig($child)) {
            $child = $parent;
            unset($parent['extends']);
            $hierarchy[] = $parent;
        }

        return array(call_user_func_array('array_replace_recursive', array_reverse($hierarchy)));
    }

    /**
     * @param array $config Config
     *
     * @return array
     */
    private function getParentConfig(array $config)
    {
        return isset($config['extends']) ? $this->getConfig($config['extends']) : null;
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function getConfig($pathname)
    {
        $realPathname = $this->resolveRealPathname($pathname);

        $content = @file_get_contents($realPathname);

        if (false === $content) {
            throw new ConfigurationException(sprintf('Unable to get content of configuration file "%s".', $realPathname));
        }
        try {
            $config = Yaml::parse($content);

            if (!is_array($config)) {
                throw new ConfigurationException(
                    sprintf('Configuration file "%s" must contain array, "%s" provided.', $realPathname, gettype($config))
                );
            }

            return $config;
        } catch (ParseException $ex) {
            throw new ConfigurationException(
                sprintf('Unable to parse configuration file "%s": "%s".', $realPathname, $ex->getMessage())
            );
        }
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return string
     */
    private function resolveRealPathname($pathname)
    {
        if (0 !== strpos($pathname, '@')) {
            return $pathname;
        }
        foreach ($this->bundles as $name => $class) {
            if (0 !== strpos($pathname, '@'.$name)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($class);

            return dirname($reflectionClass->getFileName()).str_replace('@'.$name, '', $pathname);
        }

        return $pathname;
    }
}
