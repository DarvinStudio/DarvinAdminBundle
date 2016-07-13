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
class ObjectPermissions implements \Serializable
{
    const OBJECT_PERMISSIONS_CLASS = __CLASS__;

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var \Darvin\AdminBundle\Security\Permissions\UserPermissions[]
     */
    private $userPermissionsSet;

    /**
     * @param string $objectClass Object class
     */
    public function __construct($objectClass)
    {
        $this->objectClass = $objectClass;
        $this->userPermissionsSet = [];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
            $this->objectClass,
            $this->userPermissionsSet,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->objectClass,
            $this->userPermissionsSet
        ) = unserialize($serialized);
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
     * @param \Darvin\AdminBundle\Security\Permissions\UserPermissions[] $userPermissionsSet userPermissionsSet
     *
     * @return ObjectPermissions
     */
    public function setUserPermissionsSet(array $userPermissionsSet)
    {
        $this->userPermissionsSet = $userPermissionsSet;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Security\Permissions\UserPermissions[]
     */
    public function getUserPermissionsSet()
    {
        return $this->userPermissionsSet;
    }

    /**
     * @param int                                                      $userId          User ID
     * @param \Darvin\AdminBundle\Security\Permissions\UserPermissions $userPermissions User permissions
     *
     * @return ObjectPermissions
     */
    public function addUserPermissions($userId, UserPermissions $userPermissions)
    {
        $this->userPermissionsSet[$userId] = $userPermissions;

        return $this;
    }

    /**
     * @param int $userId User ID
     *
     * @return ObjectPermissions
     */
    public function removeUserPermissions($userId)
    {
        unset($this->userPermissionsSet[$userId]);

        return $this;
    }

    /**
     * @param int $userId User ID
     *
     * @return bool
     */
    public function hasUserPermissions($userId)
    {
        return isset($this->userPermissionsSet[$userId]);
    }
}
