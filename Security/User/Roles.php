<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\User;

/**
 * User roles
 */
final class Roles
{
    const ROLE_ADMIN      = 'ROLE_ADMIN';
    const ROLE_GUESTADMIN = 'ROLE_GUESTADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    /**
     * @var string[]
     */
    private static $roles = [
        self::ROLE_ADMIN,
        self::ROLE_GUESTADMIN,
        self::ROLE_SUPERADMIN,
    ];

    /**
     * @return string[]
     */
    public static function getRoles()
    {
        return self::$roles;
    }
}
