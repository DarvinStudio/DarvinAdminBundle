<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

/**
 * Entity security configuration
 */
class EntitySecurityConfiguration extends AbstractSecurityConfiguration
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param string $name        Configuration name
     * @param string $entityName  Entity name
     * @param string $entityClass Entity class
     */
    public function __construct($name, $entityName, $entityClass)
    {
        parent::__construct();

        $this->name = $name;
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSecurableObjectClasses()
    {
        return [
            $this->entityName => $this->entityClass,
        ];
    }
}
