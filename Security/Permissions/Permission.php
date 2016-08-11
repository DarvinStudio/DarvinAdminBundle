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

use Darvin\AdminBundle\Security\User\Roles;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Permission
 */
final class Permission
{
    const CREATE_DELETE = 'create_delete';
    const EDIT          = 'edit';
    const VIEW          = 'view';

    /**
     * @var string[]
     */
    private static $permissions = [
        self::CREATE_DELETE,
        self::EDIT,
        self::VIEW,
    ];

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user User
     *
     * @return string[]
     */
    public static function getDefaultPermissions(UserInterface $user)
    {
        return array_fill_keys(self::getAllPermissions(), !in_array(Roles::ROLE_GUESTADMIN, $user->getRoles()));
    }

    /**
     * @return string[]
     */
    public static function getAllPermissions()
    {
        return self::$permissions;
    }
}
