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
     * @var string
     */
    private $config;

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
     * @param string $alias  Alias
     * @param string $entity Entity
     * @param string $config Config
     */
    public function __construct($alias, $entity, $config)
    {
        $this->alias = $alias;
        $this->entity = $entity;
        $this->config = $config;

        $this->metadataId = 'darvin_admin.metadata.section.'.$alias;
        $this->securityConfigId = 'darvin_admin.security.configuration.section.'.$alias;
        $this->securityConfigName = sprintf('darvin_admin_%s_security', $alias);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getMetadataId()
    {
        return $this->metadataId;
    }

    /**
     * @return string
     */
    public function getSecurityConfigId()
    {
        return $this->securityConfigId;
    }

    /**
     * @return string
     */
    public function getSecurityConfigName()
    {
        return $this->securityConfigName;
    }
}
