<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Permissions;

/**
 * Object permissions
 */
class ObjectPermissions
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var \Darvin\AdminBundle\Security\Permissions\AdministratorPermissions[]
     */
    private $administratorPermissionsSet;

    /**
     * @param string $objectClass Object class
     */
    public function __construct($objectClass)
    {
        $this->objectClass = $objectClass;
        $this->administratorPermissionsSet = array();
    }

    /**
     * @param string $objectClass objectClass
     *
     * @return ObjectPermissions
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * @param \Darvin\AdminBundle\Security\Permissions\AdministratorPermissions[] $administratorPermissionsSet administratorPermissionsSet
     *
     * @return ObjectPermissions
     */
    public function setAdministratorPermissionsSet(array $administratorPermissionsSet)
    {
        $this->administratorPermissionsSet = $administratorPermissionsSet;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Security\Permissions\AdministratorPermissions[]
     */
    public function getAdministratorPermissionsSet()
    {
        return $this->administratorPermissionsSet;
    }

    /**
     * @param string                                                            $administratorUsername    Administrator username
     * @param \Darvin\AdminBundle\Security\Permissions\AdministratorPermissions $administratorPermissions Administrator permissions
     *
     * @return ObjectPermissions
     */
    public function addAdministratorPermissions($administratorUsername, AdministratorPermissions $administratorPermissions)
    {
        $this->administratorPermissionsSet[$administratorUsername] = $administratorPermissions;

        return $this;
    }

    /**
     * @param string $administratorUsername Administrator username
     *
     * @return ObjectPermissions
     */
    public function removeAdministratorPermissions($administratorUsername)
    {
        unset($this->administratorPermissionsSet[$administratorUsername]);

        return $this;
    }

    /**
     * @param string $administratorUsername Administrator username
     *
     * @return bool
     */
    public function hasAdministratorPermissions($administratorUsername)
    {
        return isset($this->administratorPermissionsSet[$administratorUsername]);
    }
}
