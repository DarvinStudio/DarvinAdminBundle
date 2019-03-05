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
 * User permissions
 */
class UserPermissions implements \Serializable
{
    /**
     * @var mixed
     */
    private $userId;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param mixed $userId      User ID
     * @param array $permissions Permissions
     */
    public function __construct($userId, array $permissions)
    {
        $this->userId = $userId;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([
            $this->userId,
            $this->permissions,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list(
            $this->userId,
            $this->permissions
        ) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId userId
     *
     * @return UserPermissions
     */
    public function setUserId($userId): UserPermissions
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions permissions
     *
     * @return UserPermissions
     */
    public function setPermissions(?array $permissions): UserPermissions
    {
        $this->permissions = $permissions;

        return $this;
    }
}
