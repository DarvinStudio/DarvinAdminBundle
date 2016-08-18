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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Configuration loader
 */
class ConfigurationLoader
{
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var array
     */
    private $bundles;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag Parameter bag
     * @param array                                                                     $bundles      List of bundles
     * @param string                                                                    $rootDir      Root directory
     */
    public function __construct(ParameterBagInterface $parameterBag, array $bundles, $rootDir)
    {
        $this->parameterBag = $parameterBag;
        $this->bundles = $bundles;
        $this->rootDir = $rootDir;
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

        return $this->processConfiguration($this->getMergedConfig($pathname), $pathname);
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
            return $this->parameterBag->resolveValue($processor->processConfiguration(new Configuration(), $config));
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
    private function getMergedConfig($pathname)
    {
        $hierarchy = [];

        $childPathname = $pathname;
        $childRealPathname = $this->resolveRealPathname($childPathname, true);

        $hierarchy[] = $child = $this->getConfig($childRealPathname);

        while (isset($child['extends'])) {
            $parentPathname = $child['extends'];
            $parentRealPathname = $this->resolveRealPathname($parentPathname, $parentPathname !== $childPathname);

            if ($parentRealPathname === $childRealPathname) {
                throw new ConfigurationException(sprintf('Configuration file "%s" tried to extend itself.', $childRealPathname));
            }

            $childPathname = $parentPathname;
            $childRealPathname = $parentRealPathname;

            $hierarchy[] = $child = $this->getConfig($childRealPathname);
        }
        foreach ($hierarchy as &$config) {
            unset($config['extends']);
        }

        unset($config);

        return [call_user_func_array('array_replace_recursive', array_reverse($hierarchy))];
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function getConfig($pathname)
    {
        $content = file_get_contents($pathname);

        if (false === $content) {
            throw new ConfigurationException(sprintf('Unable to get content of configuration file "%s".', $pathname));
        }
        try {
            return (array) Yaml::parse($content);
        } catch (ParseException $ex) {
            throw new ConfigurationException(
                sprintf('Unable to parse configuration file "%s": "%s".', $pathname, $ex->getMessage())
            );
        }
    }

    /**
     * @param string $pathname      Configuration file pathname
     * @param bool   $allowOverride Whether to allow to override configuration file
     *
     * @return string
     */
    private function resolveRealPathname($pathname, $allowOverride = false)
    {
        if (0 !== strpos($pathname, '@')) {
            return $pathname;
        }
        foreach ($this->bundles as $name => $class) {
            if (0 !== strpos($pathname, '@'.$name)) {
                continue;
            }

            $path = str_replace('@'.$name.'/', '', $pathname);

            if ($allowOverride) {
                $parts = explode('/', $path);

                if (!empty($parts)) {
                    $overridden = implode('/', array_merge([$this->rootDir, array_shift($parts), $name], $parts));

                    if (file_exists($overridden)) {
                        return $overridden;
                    }
                }
            }

            return dirname((new \ReflectionClass($class))->getFileName()).'/'.$path;
        }

        return $pathname;
    }
}
