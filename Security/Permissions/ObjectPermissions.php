<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
    public function __construct(string $objectClass)
    {
        $this->objectClass = $objectClass;
        $this->userPermissionsSet = [];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([
            $this->objectClass,
            $this->userPermissionsSet,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->objectClass,
            $this->userPermissionsSet
        ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    /**
     * @param string $objectClass objectClass
     *
     * @return ObjectPermissions
     */
    public function setObjectClass(?string $objectClass): ObjectPermissions
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * @return \Darvin\AdminBundle\Security\Permissions\UserPermissions[]
     */
    public function getUserPermissionsSet(): ?array
    {
        return $this->userPermissionsSet;
    }

    /**
     * @param \Darvin\AdminBundle\Security\Permissions\UserPermissions[] $userPermissionsSet userPermissionsSet
     *
     * @return ObjectPermissions
     */
    public function setUserPermissionsSet(?array $userPermissionsSet): ObjectPermissions
    {
        $this->userPermissionsSet = $userPermissionsSet;

        return $this;
    }

    /**
     * @param mixed                                                    $userId          User ID
     * @param \Darvin\AdminBundle\Security\Permissions\UserPermissions $userPermissions User permissions
     *
     * @return ObjectPermissions
     */
    public function addUserPermissions($userId, UserPermissions $userPermissions): ObjectPermissions
    {
        $this->userPermissionsSet[$userId] = $userPermissions;

        return $this;
    }

    /**
     * @param mixed $userId User ID
     *
     * @return ObjectPermissions
     */
    public function removeUserPermissions($userId): ObjectPermissions
    {
        unset($this->userPermissionsSet[$userId]);

        return $this;
    }

    /**
     * @param mixed $userId User ID
     *
     * @return bool
     */
    public function hasUserPermissions($userId): bool
    {
        return isset($this->userPermissionsSet[$userId]);
    }
}
