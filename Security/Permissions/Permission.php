<?php declare(strict_types=1);
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
use Darvin\UserBundle\Entity\BaseUser;

/**
 * Permission
 */
final class Permission
{
    public const PREFIX = 'admin_';

    public const CREATE_DELETE = self::PREFIX.'create_delete';
    public const EDIT          = self::PREFIX.'edit';
    public const VIEW          = self::PREFIX.'view';

    /**
     * @param \Darvin\UserBundle\Entity\BaseUser $user User
     *
     * @return array
     */
    public static function getDefaultPermissions(BaseUser $user): array
    {
        return array_fill_keys(self::getAllPermissions(), !in_array(Roles::ROLE_GUESTADMIN, $user->getRoles()));
    }

    /**
     * @return string[]
     */
    public static function getAllPermissions(): array
    {
        return [
            self::CREATE_DELETE,
            self::EDIT,
            self::VIEW,
        ];
    }
}
