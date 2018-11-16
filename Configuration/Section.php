<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Configuration;

/**
 * Section
 */
class Section
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string|null
     */
    private $config;

    /**
     * @var string
     */
    private $controllerId;

    /**
     * @var string
     */
    private $metadataId;

    /**
     * @var string
     */
    private $securityConfigId;

    /**
     * @var string
     */
    private $securityConfigName;

    /**
     * @param string      $alias  Alias
     * @param string      $entity Entity
     * @param string|null $config Config
     */
    public function __construct(string $alias, string $entity, ?string $config = null)
    {
        $this->alias = $alias;
        $this->entity = $entity;
        $this->config = $config;

        $this->controllerId = sprintf('darvin_admin.section.%s.controller', $alias);
        $this->metadataId = sprintf('darvin_admin.section.%s.metadata', $alias);
        $this->securityConfigId = sprintf('darvin_admin.section.%s.security_configuration', $alias);
        $this->securityConfigName = sprintf('darvin_admin_%s_security', $alias);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return string|null
     */
    public function getConfig(): ?string
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getControllerId(): string
    {
        return $this->controllerId;
    }

    /**
     * @return string
     */
    public function getMetadataId(): string
    {
        return $this->metadataId;
    }

    /**
     * @return string
     */
    public function getSecurityConfigId(): string
    {
        return $this->securityConfigId;
    }

    /**
     * @return string
     */
    public function getSecurityConfigName(): string
    {
        return $this->securityConfigName;
    }
}
