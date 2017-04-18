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

use Psr\Log\LoggerInterface;
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

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
     * @param \Psr\Log\LoggerInterface                                                  $logger       Logger
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag Parameter bag
     * @param array                                                                     $bundles      List of bundles
     * @param string                                                                    $rootDir      Root directory
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $parameterBag, array $bundles, $rootDir)
    {
        $this->logger = $logger;
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
        foreach ($hierarchy as $key => $config) {
            unset($hierarchy[$key]['extends']);
        }

        $merged = [];

        foreach (array_reverse($hierarchy) as $key => $config) {
            if (0 === $key) {
                $config = $this->mergeConfigParams($config);
            }

            $merged = $this->mergeConfigs($merged, $config);
        }

        return [$merged];
    }

    /**
     * @param array $config Config
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function mergeConfigParams(array $config)
    {
        foreach ($config as $name => $value) {
            preg_match('/^extends~(.*)~(.*)$/', $name, $matches);

            if (3 === count($matches)) {
                if (!isset($config[$matches[1]])) {
                    throw new ConfigurationException(sprintf('Unable to find parameter "%s" for extending.', $matches[1]));
                }
                if (!is_array($config[$matches[1]])) {
                    throw new ConfigurationException(
                        sprintf('Unable to extend parameter "%s": only array parameters can be extended.', $matches[1])
                    );
                }

                $config[$matches[2]] = $this->mergeConfigs($config[$matches[1]], $value);

                unset($config[$name]);

                continue;
            }
            if (is_array($value)) {
                $config[$name] = $this->mergeConfigParams($value);
            }
        }

        return $config;
    }

    /**
     * @param array $first  First config
     * @param array $second Second config
     *
     * @return array
     * @throws \Darvin\AdminBundle\Metadata\Configuration\ConfigurationException
     */
    private function mergeConfigs(array $first, array $second)
    {
        foreach ($second as $name => $value) {
            if (0 === strpos($name, 'override~')) {
                $name = preg_replace('/^override~/', '', $name);

                if (!array_key_exists($name, $first)) {
                    $this->logger->warning(sprintf('Unable to find parameter "%s" for overriding.', $name));

                    continue;
                }
                if (!is_array($first[$name])) {
                    throw new ConfigurationException(
                        sprintf('Unable to override parameter "%s": only array parameters can be overridden.', $name)
                    );
                }

                $first[$name] = $second['override~'.$name];

                continue;
            }
            if (0 === strpos($name, 'remove~')) {
                $name = preg_replace('/^remove~/', '', $name);

                if (!array_key_exists($name, $first)) {
                    $this->logger->warning(sprintf('Unable to find parameter "%s" for removal.', $name));

                    continue;
                }

                unset($first[$name]);

                continue;
            }

            preg_match('/^(after|before)~(.*)~(.*)$/', $name, $matches);

            if (4 === count($matches)) {
                list(, $position, $target, $name) = $matches;

                if (!array_key_exists($target, $first)) {
                    $this->logger->warning(sprintf('Unable to find parameter "%s" to place %s it.', $target, $position));

                    $first[$name] = $value;

                    continue;
                }

                $replacement = [];

                foreach ($first as $n => $v) {
                    if ('before' === $position && $n === $target) {
                        $replacement[$name] = $value;
                    }

                    $replacement[$n] = $v;

                    if ('after' === $position && $n === $target) {
                        $replacement[$name] = $value;
                    }
                }

                $first = $replacement;

                continue;
            }
            if (!array_key_exists($name, $first)) {
                $first[$name] = $value;

                continue;
            }

            $first[$name] = is_array($first[$name]) && is_array($second[$name])
                ? $this->mergeConfigs($first[$name], $second[$name])
                : $second[$name];
        }

        return $first;
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
