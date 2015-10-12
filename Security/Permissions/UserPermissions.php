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
 * User permissions
 */
class UserPermissions
{
    const USER_PERMISSIONS_CLASS = __CLASS__;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param int   $userId      User ID
     * @param array $permissions Permissions
     */
    public function __construct($userId, array $permissions)
    {
        $this->userId = $userId;
        $this->permissions = $permissions;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId userId
     *
     * @return UserPermissions
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions permissions
     *
     * @return UserPermissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }
}
